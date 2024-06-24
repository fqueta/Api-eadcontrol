<?php

use App\Http\Controllers\api\EmpresasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ddiController;
use App\Http\Controllers\MatriculasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\TesteController;
use App\Http\Middleware\TenancyMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get('/ddi',[ddiController::class,'index'])->name('index');
Route::resource('ddi','\App\Http\Controllers\DdisController',['parameters' => [
    'ddi' => 'id'
]]);
Route::prefix('v1')->group(function(){
    // Route::get('/matriculas',[MatriculasController::class,'index'])->middleware('auth:sanctum');
    // Route::get('/matriculas/{id}',[MatriculasController::class,'show'])->middleware('auth:sanctum');
    // Route::put('/matriculas/{id}',[MatriculasController::class,'update'])->middleware('auth:sanctum');
    Route::get('/usuarios',[EmpresasController::class,'index'])->name('usuarios');
    Route::middleware(TenancyMiddleware::class)->group(function () {
        Route::post('/login',[AuthController::class,'login']);
        Route::middleware('auth:sanctum')->get('/user', [AuthController::class,'user']);
        Route::get('/matriculas',[MatriculasController::class,'index'])->name('matriculas')->middleware('auth:sanctum');
        Route::resource('cursos','\App\Http\Controllers\CursosController',['parameters' => [
            'cursos' => 'id'
        ]])->middleware('auth:sanctum');
    });
});

