<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Ticket;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;

class ActivityLog extends Page
{
    protected string $view = 'filament.pages.activity-log';
    protected static ?string $slug = 'activities/{ticketNumber}';
    protected static bool $shouldRegisterNavigation = false;
    public ?string $ticketNumber = null;
    public ?Ticket $ticket = null;
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('administrator');
    }

    public function mount(string $ticketNumber): void
    {
        $this->ticketNumber = $ticketNumber;
        
        $this->ticket = Ticket::withTrashed()->where('ticket_number', $ticketNumber)->firstOrFail();
    }

    public function getHeading(): string
    {
        return 'History for ' . $this->ticketNumber;
    }
}