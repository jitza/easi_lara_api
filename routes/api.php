<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Models\PersonalInfo;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/user', function (Request $r) {
    return $r->user()->load(['personalInfo', 'user_usernames']);
    
});
Route::middleware('auth:sanctum')->get('/menu', [MenuController::class, 'userMenu']);
Route::get('/auth/token/{key}', [AuthController::class, 'getToken']);
// roles 


// Roles
Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::put('/roles/{id}', [RoleController::class, 'update']);
Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

// Role Permissions
Route::get('/role-permissions/{roleId}', [RoleController::class, 'getPermissions']);
Route::put('/role-permissions/{roleId}', [RoleController::class, 'updatePermissions']);
