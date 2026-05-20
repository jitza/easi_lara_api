<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Models\PersonalInfo;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ManageAcademicYearController;
use App\Http\Controllers\ManageSemesterController;
use App\Http\Controllers\SemesterStatusController;
use App\Http\Controllers\ManageGraduationController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/user', function (Request $r) {
    return $r->user()->load(['personalInfo', 'user_usernames']);
    
});
Route::middleware('auth:sanctum')->get('/menu', [MenuController::class, 'userMenu']);
Route::get('/auth/token/{key}', [AuthController::class, 'getToken']);

// Roles
Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::put('/roles/{id}', [RoleController::class, 'update']);
Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

// Role Permissions
Route::get('/role-permissions/{roleId}', [RoleController::class, 'getPermissions']);
Route::put('/role-permissions/{roleId}', [RoleController::class, 'updatePermissions']);

// User Management
Route::get('/users', [UserManagementController::class, 'index']);
Route::put('/users/{id}/status', [UserManagementController::class, 'updateStatus']);
Route::put('/users/{id}/role', [UserManagementController::class, 'updateRole']);

//academic years
Route::get('/academic-years', [ManageAcademicYearController::class, 'index']);
Route::post('/academic-years', [ManageAcademicYearController::class, 'store']);
Route::put('/academic-years/{id}', [ManageAcademicYearController::class, 'update']);
Route::delete('/academic-years/{id}', [ManageAcademicYearController::class, 'destroy']);

// semesters
Route::get('/semesters', [ManageSemesterController::class, 'index']);
Route::post('/semesters', [ManageSemesterController::class, 'store']);
Route::put('/semesters/{id}', [ManageSemesterController::class, 'update']);
Route::delete('/semesters/{id}', [ManageSemesterController::class, 'destroy']);
// semester status
Route::get('/semester-status', [SemesterStatusController::class, 'getStatuses']);
// graduation dates
Route::get('/graduation-dates', [ManageGraduationController::class, 'index']);
Route::post('/graduation-dates', [ManageGraduationController::class, 'store']);
Route::put('/graduation-dates/{id}', [ManageGraduationController::class, 'update']);
Route::delete('/graduation-dates/{id}', [ManageGraduationController::class, 'destroy']);
// semesters for dropdown

