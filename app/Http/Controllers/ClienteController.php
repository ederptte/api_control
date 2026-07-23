<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;




class ClienteController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = Cliente::latest();

        // 2. Verificamos si el usuario envió el parámetro 'buscar' en la URL
        if ($request->has('buscar') && $request->filled('buscar')) {
            $busqueda = str_replace(['%', '_'], ['\%', '\_'], $request->input('buscar'));

            // Modificamos la consulta usando condiciones 'OR'
            // El operador 'LIKE' sirve para buscar coincidencias parciales
            // El '%' indica que puede haber texto antes o después de la palabra
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'LIKE', '%' . $busqueda . '%')
                  ->orWhere('email', 'LIKE', '%' . $busqueda . '%')
                  ->orWhere('whatsapp', 'LIKE', '%' . $busqueda . '%');
            });
        }

        $clientes = $query->paginate(10);

        // Aplicamos el estilo a toda la lista
        return ClienteResource::collection($clientes);

    }

    public function store(StoreClienteRequest $request): JsonResponse
    {

        $cliente = Cliente::create($request->validated());

        return response()->json([
            'mensaje' => 'Registro exitoso',
            'data' => new ClienteResource($cliente),
            ], 201);
    }

    public function show(Cliente $cliente): ClienteResource
    {
        return new ClienteResource($cliente);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): JsonResponse
    {

        // 3. Actualizar el registro en la base de datos usando Eloquent
        $cliente->update($request->validated());

        // 4. Retornar la respuesta JSON de éxito
        return response()->json([
            'mensaje' => 'Cliente actualizado con éxito.',
            'data'    => new ClienteResource($cliente),
        ], 200); // Código HTTP 200 = Éxito / OK
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        // 1. Ejecutar el borrado en la base de datos usando Eloquent
        // Tras bambalinas, Laravel ejecuta: DELETE FROM clientes WHERE id = X;
        $cliente->delete();

        // 2. Responder al cliente con la confirmación del borrado
        return response()->json([
            'mensaje' => 'El cliente fue eliminado correctamente de la base de datos.'
        ], 200); // Código HTTP 200 = OK / Éxito
    }


}
