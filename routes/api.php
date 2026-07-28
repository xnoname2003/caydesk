<?php

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
use App\Http\Controllers\Api\TicketActions\UpdateTicketStatusController;
use App\Http\Controllers\Api\TicketActions\UpdateTicketPriorityController;
use App\Http\Controllers\Api\TicketActions\ManageTicketLabelsController;
use App\Http\Controllers\Api\TicketActions\AssignTicketAgentController;
use App\Http\Controllers\Api\TicketActions\CloseTicketController;
use App\Http\Controllers\Api\TicketActions\ReopenTicketController;
use App\Http\Controllers\Api\TicketActions\SubmitTicketReplyController;

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
    Route::get('/tickets/{ticket_number}', [TicketController::class, 'show']);
    Route::post('/tickets/create', [TicketController::class, 'store']);
    Route::delete('/tickets/{ticket_number}/delete', [TicketController::class, 'destroy']);

    // For updating specific attributes of a ticket
    Route::put('/tickets/{ticket_number}/priority/edit', UpdateTicketPriorityController::class);
    Route::put('/tickets/{ticket_number}/labels/edit', ManageTicketLabelsController::class);

    Route::put('/tickets/{ticket_number}/status/edit', UpdateTicketStatusController::class);
    Route::put('/tickets/{ticket_number}/status/close', CloseTicketController::class);
    Route::put('/tickets/{ticket_number}/status/reopen', ReopenTicketController::class);

    Route::put('/tickets/{ticket_number}/agent/assign', AssignTicketAgentController::class);
    Route::post('/tickets/{ticket_number}/comment/reply', SubmitTicketReplyController::class);
    
});