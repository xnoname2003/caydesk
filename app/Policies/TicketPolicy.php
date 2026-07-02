<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    private function isAdminOrSupervisor(User $user): bool
    {
        return $user->hasAnyRole([
            'administrator', 'supervisor'
        ]);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // 1. Admin & Supervisor bebas melihat tiket mana saja
        if ($this->isAdminOrSupervisor($user)) {
            return true;
        }

        // 2. Agent HANYA boleh melihat tiket yang di-assign ke dia
        if ($user->hasRole('agent')) {
            return $ticket->assigned_agent_id === $user->id;
        }

        // 3. Customer HANYA boleh melihat tiket yang dia buat sendiri
        // (Sesuaikan nama kolom 'user_id' atau 'created_by' dengan database lo)
        if ($user->hasRole('customer')) {
            return $ticket->created_by === $user->id; 
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        if ($this->isAdminOrSupervisor($user)) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $ticket->assigned_agent_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return auth()->user()->hasRole('administrator');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return auth()->user()->hasRole('administrator');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return auth()->user()->hasRole('administrator');
    }
}
