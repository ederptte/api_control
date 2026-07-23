<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCuentaRequest;
use App\Http\Requests\UpdateCuentaRequest;
use App\Http\Resources\CuentaResource;
use App\Http\Resources\VentaResource;
use App\Models\Venta;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class CuentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = Cuenta::latest();

        // 2. Verificamos si el usuario envió el parámetro 'buscar' en la URL
        if ($request->has('buscar') && $request->filled('buscar')) {
            $busqueda = str_replace(['%', '_'], ['\%', '\_'], $request->input('buscar'));

            // Modificamos la consulta usando condiciones 'OR'
            // El operador 'LIKE' sirve para buscar coincidencias parciales
            // El '%' indica que puede haber texto antes o después de la palabra
            $query->where(function($q) use ($busqueda) {
                $q->where('plataforma', 'LIKE', '%' . $busqueda . '%')
                  ->orWhere('correo', 'LIKE', '%' . $busqueda . '%');

            });
        }

        $cuentas = $query->paginate(10);

        // Aplicamos el estilo a toda la lista
        return CuentaResource::collection($cuentas);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCuentaRequest $request): JsonResponse
    {
        $cuenta = Cuenta::create($request->validated());

        return response()->json([
            'mensaje' => 'Registro exitoso',
            'data' => new CuentaResource($cuenta),
            ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Cuenta $cuenta)
    {
        return new CuentaResource($cuenta);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCuentaRequest $request, Cuenta $cuenta)
    {

        // 3. Actualizar el registro en la base de datos usando Eloquent
        $cuenta->update($request->validated());

        // 4. Retornar la respuesta JSON de éxito
        return response()->json([
            'mensaje' => 'Cuenta actualizada con éxito.',
            'data'    => new CuentaResource($cuenta),
        ], 200); // Código HTTP 200 = Éxito / OK
    }

    public function clientesActivos($id)
    {
        $ventas = Venta::whereHas('perfilCuenta', function ($q) use ($id) {
                $q->where('estado', 'vendido')
                ->whereHas('compra', function ($q2) use ($id) {
                    $q2->where('cuenta_id', $id);
                });
            })
            ->with(['cliente', 'perfilCuenta.compra.cuenta'])
            ->orderByDesc('fecha_venta')
            ->get()
            ->unique('perfil_cuenta_id'); // por si un perfil tuvo varias ventas (renovaciones), solo la más reciente

        return VentaResource::collection($ventas);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cuenta $cuenta)
    {
        // 1. Ejecutar el borrado en la base de datos usando Eloquent
        // Tras bambalinas, Laravel ejecuta: DELETE FROM cuentas WHERE id = X;
        $cuenta->delete();

        // 2. Responder la cuenta con la confirmación del borrado
        return response()->json([
            'mensaje' => 'la cuenta fue eliminada correctamente de la base de datos.'
        ], 200); // Código HTTP 200 = OK / Éxito
    }
}
