<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Ticket;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use App\Filament\Pages\Tickets\Actions\CreateTicketAction;
use App\Filament\Pages\Tickets\Schemas\TicketTableSchema;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use App\Services\TicketStatusService;
use Filament\Tables\Components\Tab;

class Tickets extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.tickets';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;
    protected static ?string $navigationLabel = 'Tickets';
    protected static ?string $title = 'Ticket Board';
    protected static ?int $navigationSort = 1;
    public string $activeTab = 'all';
    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function getTitle(): string|Htmlable
    {
        return __('Ticket Management');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateTicketAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Ticket::query()->with(['priority', 'category', 'creator', 'assignedAgent']);
                $user = auth()->user();
                if ($user->hasRole('agent')) {
                    $query->where('assigned_agent_id', $user->id);
                } elseif ($user->hasRole('customer')) {
                    $query->where('created_by', $user->id);
                } elseif (!$user->hasAnyRole(['administrator', 'supervisor'])) {
                    $query->whereRaw('1 = 0');
                }
                if ($this->activeTab === 'active') {
                    $query->whereNotIn('status', [TicketStatusService::STATUS_RESOLVED, TicketStatusService::STATUS_CLOSED]);
                } elseif ($this->activeTab === 'my_tickets') {
                    $query->where('assigned_agent_id', $user->id);
                } elseif ($this->activeTab === 'escalated') {
                    $query->where('status', TicketStatusService::STATUS_ESCALATED);
                } elseif ($this->activeTab === 'resolved') {
                    $query->whereIn('status', [TicketStatusService::STATUS_RESOLVED, TicketStatusService::STATUS_CLOSED]);
                } elseif ($this->activeTab === 'overdue') {
                    $query->where('due_at', '<', now())->whereNotIn('status', [TicketStatusService::STATUS_RESOLVED, TicketStatusService::STATUS_CLOSED]);
                }
                return $query;
            })
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->columns(TicketTableSchema::columns())
            ->filters(TicketTableSchema::filters())
            ->actions(TicketTableSchema::actions())
            ->recordUrl(
                fn(Ticket $record): string => url('/app/tickets/' . $record->ticket_number)
            );
    }
}