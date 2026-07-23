<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\AuthController;


use Illuminate\Support\Facades\Route;

// Ruta pública para loguearse
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('cuentas', CuentaController::class);
    Route::apiResource('compras', CompraController::class);
    Route::apiResource('ventas', VentaController::class);

    Route::post('compras/{compra}/renovar', [CompraController::class, 'renovar']);
    Route::post('ventas/renovar', [VentaController::class, 'renovar']);
    Route::post('perfiles/{id}/liberar', [VentaController::class, 'liberarPerfil']);
    Route::post('ventas/{venta}/cambiar-perfil', [VentaController::class, 'cambiarPerfil']);
    Route::put('perfiles/{id}/pin', [VentaController::class, 'actualizarPin']);
    Route::get('cuentas/{id}/clientes-activos', [CuentaController::class, 'clientesActivos']);

    // Aquí adentro vas a meter las rutas de tu inventario, almacenes, etc.
    // Ejemplo: Route::apiResource('productos', ProductoController::class);
});


