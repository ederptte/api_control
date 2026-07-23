<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\RenovarVentaRequest;
use App\Http\Resources\VentaResource;
use App\Models\PerfilCuenta;
use App\Models\Venta;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'perfilCuenta.compra.cuenta']);

        if ($request->has('buscar') && $request->filled('buscar')) {
            $busqueda = str_replace(['%', '_'], ['\%', '\_'], $request->input('buscar'));

            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('cliente', function ($sub) use ($busqueda) {
                    $sub->where('nombre', 'LIKE', '%' . $busqueda . '%')
                        ->orWhere('whatsapp', 'LIKE', '%' . $busqueda . '%');
                })
                ->orWhereHas('perfilCuenta.compra.cuenta', function ($sub) use ($busqueda) {
                    $sub->where('plataforma', 'LIKE', '%' . $busqueda . '%')
                        ->orWhere('correo', 'LIKE', '%' . $busqueda . '%');
                });
            });
        }

        if ($request->filled('por_vencer')) {
            $dias = (int) $request->input('por_vencer');

            $query->whereDate('fecha_vencimiento', '>=', now()->toDateString())
                ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString());
        }

        $ventas = $query->latest()->paginate(15);

        return VentaResource::collection($ventas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request): JsonResponse
    {
        $datosValidados = $request->validated();

        $perfil = PerfilCuenta::findOrFail($datosValidados['perfil_cuenta_id']);

        if ($perfil->estado !== 'disponible') {
            throw ValidationException::withMessages([
                'perfil_cuenta_id' => ['Este perfil ya se encuentra vendido o no está disponible.'],
            ]);
        }

        $venta = $this->crearVenta($datosValidados, $perfil, [
            'pin' => $request->filled('pin') ? $datosValidados['pin'] : $perfil->pin,
            'dispositivo_autorizado' => $request->filled('dispositivo_autorizado')
                ? $datosValidados['dispositivo_autorizado']
                : $perfil->dispositivo_autorizado,
        ]);

        return (new VentaResource($venta->load(['cliente', 'perfilCuenta'])))
            ->additional(['mensaje' => 'Venta registrada con éxito y perfil asignado.'])
            ->response()
            ->setStatusCode(201);
    }

    public function actualizarPin(Request $request, $id): JsonResponse
    {
        $datosValidados = $request->validate([
            'pin' => ['nullable', 'string', 'max:4'],
        ]);

        $perfil = PerfilCuenta::findOrFail($id);
        $perfil->update(['pin' => $datosValidados['pin']]);

        return response()->json([
            'mensaje' => 'PIN actualizado correctamente.',
            'perfil' => $perfil,
        ]);
    }

    /**
     * Liberar un perfil cuando el cliente no continúe (terminar la suscripción).
     * Nota: la venta física no se borra, para no alterar reportes e ingresos.
     */
    public function liberarPerfil($id): JsonResponse
    {
        $perfil = PerfilCuenta::findOrFail($id);

        if (!in_array($perfil->estado, ['vendido', 'mantenimiento'])) {
            return response()->json([
                'mensaje' => 'El perfil ya se encuentra disponible.',
            ], 400);
        }

        $perfil->update([
            'estado' => 'disponible',
            'dispositivo_autorizado' => null,
        ]);

        return response()->json([
            'mensaje' => 'El perfil ha sido liberado correctamente y está listo para una nueva venta.',
            'perfil' => $perfil,
        ]);
    }

    /**
     * Renovar el servicio de un cliente sobre un perfil que ya le pertenece.
     */
    public function renovar(RenovarVentaRequest $request): JsonResponse
    {
        $datosValidados = $request->validated();

        $perfil = PerfilCuenta::findOrFail($datosValidados['perfil_cuenta_id']);

        // El perfil debe seguir activo/vendido para poder renovarse.
        if ($perfil->estado !== 'vendido') {
            return response()->json([
                'mensaje' => 'Este perfil no está activo, no se puede renovar. Debe venderse de nuevo.',
            ], 422);
        }

        $ultimaVenta = Venta::where('perfil_cuenta_id', $perfil->id)
            ->orderBy('fecha_vencimiento', 'desc')
            ->first();

        if ($ultimaVenta && $ultimaVenta->cliente_id != $datosValidados['cliente_id']) {
            return response()->json([
                'mensaje' => 'No puedes renovar este perfil porque pertenece a otro cliente.',
            ], 422);
        }

        $venta = $this->crearVenta($datosValidados, $perfil);

        return (new VentaResource($venta->load(['cliente', 'perfilCuenta'])))
            ->additional(['mensaje' => 'Servicio renovado con éxito. Pago registrado.'])
            ->response();
    }

    /**
     * Crea la venta y actualiza el perfil dentro de una transacción.
     * $extra permite pasar campos adicionales del perfil (pin, dispositivo) sin repetir la transacción en cada método.
     */
    private function crearVenta(array $datosValidados, PerfilCuenta $perfil, array $extra = []): Venta
    {
        return DB::transaction(function () use ($datosValidados, $perfil, $extra) {
            $nuevaVenta = Venta::create([
                'cliente_id' => $datosValidados['cliente_id'],
                'perfil_cuenta_id' => $datosValidados['perfil_cuenta_id'],
                'precio_venta' => $datosValidados['precio_venta'],
                'fecha_venta' => $datosValidados['fecha_venta'],
            ]);

            $perfil->update(array_merge(['estado' => 'vendido'], $extra));

            return $nuevaVenta;
        });
    }

    /**
     * Cambia el perfil asignado a una venta existente (ej. cuando el perfil original falla),
     * sin crear una venta nueva ni tocar fecha_venta/fecha_vencimiento.
     * El perfil viejo pasa a 'mantenimiento'; el nuevo pasa a 'vendido'.
     */
    public function cambiarPerfil(Request $request, Venta $venta): JsonResponse
    {
        $datosValidados = $request->validate([
            'perfil_cuenta_id_nuevo' => ['required', 'exists:perfil_cuentas,id'],
        ]);

        $perfilNuevo = PerfilCuenta::findOrFail($datosValidados['perfil_cuenta_id_nuevo']);

        if ($perfilNuevo->estado !== 'disponible') {
            return response()->json([
                'mensaje' => 'El perfil elegido no está disponible.',
            ], 422);
        }

        $ventaActualizada = DB::transaction(function () use ($venta, $perfilNuevo) {
            $perfilViejo = $venta->perfilCuenta;

            $perfilViejo->update(['estado' => 'mantenimiento']);
            $perfilNuevo->update(['estado' => 'vendido']);

            $venta->update(['perfil_cuenta_id' => $perfilNuevo->id]);

            return $venta->fresh(); // recarga el modelo con el nuevo perfil_cuenta_id
        });

        return (new VentaResource($ventaActualizada->load(['cliente', 'perfilCuenta'])))
            ->additional(['mensaje' => 'Perfil cambiado correctamente. El anterior quedó en mantenimiento.'])
            ->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta): VentaResource
    {
        return new VentaResource($venta->load(['cliente', 'perfilCuenta']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta): JsonResponse
    {
        return response()->json([
            'mensaje' => 'Una venta registrada no se puede editar. Si fue un error, anúlala y regístrala de nuevo.',
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta): JsonResponse
    {
        DB::transaction(function () use ($venta) {
            $venta->perfilCuenta()->update(['estado' => 'disponible']);
            $venta->delete(); // soft delete gracias al trait SoftDeletes
        });

        return response()->json([
            'mensaje' => 'Venta anulada correctamente. El perfil vuelve a estar disponible.',
        ], 200);
    }
}
