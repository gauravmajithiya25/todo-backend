<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('todos/trashed', [TodoController::class, 'trashed']);
Route::patch('todos/{id}/restore', [TodoController::class, 'restore']);
Route::delete('todos/{id}/force', [TodoController::class, 'forceDelete']);
Route::apiResource('todos', TodoController::class);