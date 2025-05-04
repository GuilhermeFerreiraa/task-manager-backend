<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

Route::prefix('v1')->group(function () {     
    // Rotas de autenticação
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    
    Route::middleware('auth:api')->group(function () {
        // Rotas de tarefas com endpoints específicos
        
        // Lista de tarefas e criação
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        
        // Rotas adicionais específicas (devem vir antes das rotas dinâmicas com parâmetros)
        Route::get('/tasks/high-priority', [TaskController::class, 'getHighPriorityTasks'])->name('tasks.high-priority');
        Route::get('/tasks/overdue', [TaskController::class, 'getOverdueTasks'])->name('tasks.overdue');
        
        // Rotas com parâmetros
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::patch('/tasks/{task}/complete', [TaskController::class, 'markAsCompleted'])->name('tasks.complete');
    });
});