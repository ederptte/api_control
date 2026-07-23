<?php

namespace App\Http\Controllers;

use App\Http\Requests\RenovarCompraRequest;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use App\Http\Resources\CompraResource;
use App\Models\PerfilCuenta;
use App\Models\Compra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CompraController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with(['cuenta', 'perfilCuentas'])

            ->withCount(['perfilCuentas as pantallas_vendidas' => function ($query) {
                $query->where('estado', 'vendido');
            }]);

        if ($request->has('disponibles') && $request->boolean('disponibles')) {
            $query->whereHas('perfilCuentas', function ($query) {
                $query->where('estado', 'disponible');
            });
        }

        $compras = $query->latest()->paginate(10);

        return CompraResource::collection($compras);
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        $datosValidados = $request->validated();

        $compra = DB::transaction(function () use ($datosValidados) {
            $nuevaCompra = Compra::create($datosValidados);

            for ($i = 1; $i <= $nuevaCompra->pantallas; $i++) {
                PerfilCuenta::create([
                    'compra_id' => $nuevaCompra->id,
                    'nombre_perfil' => "Perfil " . $i,
                    'pin' => '',
                    'dispositivo_autorizado' => '',
                    'estado' => 'disponible',
                ]);
            }

            return $nuevaCompra;
        });

        $compra->load(['cuenta', 'perfilCuentas']);

        return (new CompraResource($compra))
            ->additional(['mensaje' => 'Compra exitosa y perfiles generados correctamente'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Compra $compra): CompraResource
    {
        $compra->load(['cuenta', 'perfilCuentas']);
        return new CompraResource($compra);
    }

    /**
     * Update the specified resource in storage.
     * Solo permite editar precio_compra, fecha_compra y nota.
     * cuenta_id y pantallas son inmutables una vez creada la compra.
     */
    public function update(UpdateCompraRequest $request, Compra $compra): JsonResponse
    {
        if ($compra->estado === 'cancelada') {
            return response()->json([
                'mensaje' => 'No se puede editar una compra cancelada.',
            ], 422);
        }

        $compra->update($request->validated());
        $compra->load(['cuenta', 'perfilCuentas']);

        return (new CompraResource($compra))
            ->additional(['mensaje' => 'Compra actualizada correctamente.'])
            ->response();
    }

    /**
     * Remove the specified resource from storage.
     * No borra físicamente: cancela la compra y elimina sus perfiles asociados,
     * ya que cancelar implica que la compra real nunca se hizo.
     */
    public function destroy(Compra $compra): JsonResponse
    {
        if ($compra->estado === 'cancelada') {
            return response()->json([
                'mensaje' => 'Esta compra ya está cancelada.'
            ], 422);
        }

        $tieneVendidos = $compra->perfilCuentas()->where('estado', 'vendido')->exists();

        DB::transaction(function () use ($compra) {
            $compra->perfilCuentas()->delete();
            $compra->update(['estado' => 'cancelada']);
        });

        return response()->json([
            'mensaje' => $tieneVendidos
                ? 'Compra anulada. Atención: tenía perfiles vendidos que fueron eliminados.'
                : 'Compra anulada correctamente.',
        ], 200);
    }

    /**
     * Renovar una cuenta que se venció o está por vencer.
     * Crea una Compra nueva (para que cuente en los cálculos financieros),
     * moviendo a ella los perfiles ya vendidos (sin tocar su cliente ni su fecha_vencimiento propia),
     * y cerrando la compra vieja.
     */
    public function renovar(RenovarCompraRequest $request, Compra $compra): JsonResponse
    {
        $datosValidados = $request->validate([
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'fecha_compra'  => ['required', 'date'],
            'nota'          => ['nullable', 'string'],
        ]);

        $nuevaCompra = DB::transaction(function () use ($compra, $datosValidados) {
            $perfilesVendidos = $compra->perfilCuentas()->where('estado', 'vendido')->get();

            $nueva = Compra::create([
                'cuenta_id'     => $compra->cuenta_id,
                'precio_compra' => $datosValidados['precio_compra'],
                'fecha_compra'  => $datosValidados['fecha_compra'],
                'pantallas'     => $perfilesVendidos->count(),
                'nota'          => $datosValidados['nota'] ?? "Renovación de compra #{$compra->id}",
                'estado'        => 'activa',
            ]);

            // Movemos los perfiles vendidos a la nueva compra, sin tocar su estado, cliente ni fecha_vencimiento
            $compra->perfilCuentas()
                ->where('estado', 'vendido')
                ->update(['compra_id' => $nueva->id]);

            // Los perfiles que quedaron sin vender en la compra vieja ya no aplican
            $compra->perfilCuentas()->where('estado', 'disponible')->delete();

            $compra->update(['estado' => 'renovada']);

            return $nueva;
        });

        $nuevaCompra->load(['cuenta', 'perfilCuentas']);

        return (new CompraResource($nuevaCompra))
            ->additional(['mensaje' => 'Cuenta renovada correctamente.'])
            ->response();
    }
}
