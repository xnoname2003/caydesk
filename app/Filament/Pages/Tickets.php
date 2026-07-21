<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Tickets\Actions\CreateTicketAction;
use App\Filament\Pages\Tickets\Schemas\TicketTableSchema;
use App\Models\Ticket;
use App\Services\TicketStatusService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\TicketExporter;
use Filament\Actions\ExportAction;

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

    protected function getBaseTicketQuery(): Builder
    {
        $query = Ticket::query();
        $user = auth()->user();

        if ($user->hasRole('agent')) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_agent_id', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        } elseif ($user->hasRole('customer')) {
            $query->where('created_by', $user->id);
        } elseif (! $user->hasAnyRole(['administrator', 'supervisor'])) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function getBadgeCount(string $tab): int
    {
        $query = $this->getBaseTicketQuery();
        $finishedStatuses = [TicketStatusService::STATUS_RESOLVED, TicketStatusService::STATUS_CLOSED];
        $userId = auth()->id();

        return match ($tab) {
            'all' => $query->count(),
            'active' => $query->whereNotIn('status', $finishedStatuses)->count(),
            'assigned_to_me' => $query->where('assigned_agent_id', $userId)->count(),
            'created_by_me' => $query->where('created_by', $userId)->count(),
            'escalated' => $query->where('status', TicketStatusService::STATUS_ESCALATED)->count(),
            'resolved' => $query->whereIn('status', $finishedStatuses)->count(),
            'overdue' => $query->where('due_at', '<', now())->whereNotIn('status', $finishedStatuses)->count(),
            default => 0,
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = $this->getBaseTicketQuery()->with(['priority', 'category', 'creator', 'assignedAgent']);
                $finishedStatuses = [TicketStatusService::STATUS_RESOLVED, TicketStatusService::STATUS_CLOSED];

                if ($this->activeTab === 'active') {
                    $query->whereNotIn('status', $finishedStatuses);
                } elseif ($this->activeTab === 'assigned_to_me' || $this->activeTab === 'my_tickets') {
                    $query->where('assigned_agent_id', auth()->id());
                } elseif ($this->activeTab === 'created_by_me') {
                    $query->where('created_by', auth()->id());
                } elseif ($this->activeTab === 'escalated') {
                    $query->where('status', TicketStatusService::STATUS_ESCALATED);
                } elseif ($this->activeTab === 'resolved') {
                    $query->whereIn('status', $finishedStatuses);
                } elseif ($this->activeTab === 'overdue') {
                    $query->where('due_at', '<', now())->whereNotIn('status', $finishedStatuses);
                }

                return $query;
            })
            ->poll('3s')
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->exporter(TicketExporter::class)
                    ->label('Export CSV')
                    ->color('success')
                    ->icon('heroicon-m-arrow-down-tray')
            ])
            ->columns(TicketTableSchema::columns())
            ->filters(TicketTableSchema::filters())
            ->actions(TicketTableSchema::actions())
            ->recordUrl(
                fn (Ticket $record): string => url('/app/tickets/'.$record->ticket_number)
            );
    }
}
