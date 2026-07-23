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

Route::post('/login', [AuthController::class, 'login']); //done

Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']); //done

    // User Management (Admin Only)
    Route::get('/users', [UserController::class, 'index']); //done

    // User Profile
    Route::get('/profile', [UserController::class, 'showProfile']); //done
    Route::put('/profile/edit', [UserController::class, 'updateProfile']); //done

    // Read Category, Label, Priority, SLA Rule, Team, Role
    Route::get('/categories', [CategoryController::class, 'index']); //done
    Route::get('/labels', [LabelController::class, 'index']); //done
    Route::get('/priorities', [PriorityController::class, 'index']); //done
    Route::get('/sla-rules', [SlaRuleController::class, 'index']); //done
    Route::get('/teams', [TeamController::class, 'index']); //done
    Route::get('/roles', [RoleController::class, 'index']); //done

    // CRUD Ticket
    Route::get('/tickets', [TicketController::class, 'index']); //done
    Route::get('/tickets/{ticket_number}', [TicketController::class, 'show']); //done
    Route::post('/tickets/create', [TicketController::class, 'store']); //done
    Route::delete('/tickets/{ticket_number}/delete', [TicketController::class, 'destroy']); //done

    // For updating specific attributes of a ticket
    Route::put('/tickets/{ticket_number}/priority/edit', UpdateTicketPriorityController::class); //done
    Route::put('/tickets/{ticket_number}/labels/edit', ManageTicketLabelsController::class); //done

    Route::put('/tickets/{ticket_number}/status/edit', UpdateTicketStatusController::class); //done
    Route::put('/tickets/{ticket_number}/status/close', CloseTicketController::class); //done
    Route::put('/tickets/{ticket_number}/status/reopen', ReopenTicketController::class); //done

    Route::put('/tickets/{ticket_number}/agent/assign', AssignTicketAgentController::class); //done
    Route::post('/tickets/{ticket_number}/comment/reply', SubmitTicketReplyController::class); //done
    
});