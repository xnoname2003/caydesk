<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LabelController;
use App\Http\Controllers\Api\PriorityController;
use App\Http\Controllers\Api\SlaRuleController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User Management (Admin Only)
    Route::get('/users', [UserController::class, 'index']);

    // User Profile
    Route::get('/profile', [UserController::class, 'showProfile']);
    Route::put('/profile/edit', [UserController::class, 'updateProfile']);

    // Read Category, Label, Priority, SLA Rule, Team, Role
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/labels', [LabelController::class, 'index']);
    Route::get('/priorities', [PriorityController::class, 'index']);
    Route::get('/sla-rules', [SlaRuleController::class, 'index']);
    Route::get('/teams', [TeamController::class, 'index']);
    Route::get('/roles', [RoleController::class, 'index']);

    // CRUD Ticket
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets/create', [TicketController::class, 'store']);
    Route::get('/tickets/{ticket_number}', [TicketController::class, 'show']);
    Route::delete('/tickets/{ticket_number}/delete', [TicketController::class, 'destroy']);

    // For updating specific attributes of a ticket
    Route::put('/tickets/{ticket_number}/status/edit', [TicketController::class, 'updateStatus']);
    Route::put('/tickets/{ticket_number}/priority/edit', [TicketController::class, 'updatePriority']);
    Route::put('/tickets/{ticket_number}/labels/edit', [TicketController::class, 'manageLabels']);
    Route::put('/tickets/{ticket_number}/assign', [TicketController::class, 'assignAgent']);
    Route::put('/tickets/{ticket_number}/close', [TicketController::class, 'closeTicket']);
    Route::put('/tickets/{ticket_number}/reopen', [TicketController::class, 'reopenTicket']);
    Route::post('/tickets/{ticket_number}/reply', [TicketController::class, 'submitReply']);
});