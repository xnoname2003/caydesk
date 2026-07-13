<?php

namespace App\Filament\Pages\Tickets;

use Filament\Pages\Page;
use App\Models\Ticket;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Pages\Tickets\Actions\AssignAgentAction;
use App\Filament\Pages\Tickets\Actions\ManageLabelsAction;
use App\Filament\Pages\Tickets\Actions\DeleteTicketAction;
use App\Filament\Pages\Tickets\Actions\UpdateStatusAction;
use App\Filament\Pages\Tickets\Actions\UpdatePriorityAction;
use App\Filament\Pages\Tickets\Schemas\ReplyFormSchema;
use App\Filament\Pages\Tickets\Actions\SubmitReplyAction;
use App\Filament\Pages\Tickets\Actions\CloseTicketAction;
use App\Filament\Pages\Tickets\Actions\ReopenTicketAction;
use Filament\Schemas\Schema;
use Filament\Actions\Action;

class TicketDetail extends Page
{
    protected string $view = 'filament.pages.tickets.ticket-detail';
    protected static ?string $slug = 'tickets/{ticket_number}';
    protected static bool $shouldRegisterNavigation = false;

    public Ticket $ticket;

    public ?array $replyData = ['is_internal' => 0];

    public function mount($ticket_number)
    {

        $this->ticket = Ticket::with([
            'creator',
            'assignedAgent',
            'category',
            'priority',
            'comments.user',
            'comments.attachments',
            'comments.activitiesAsSubject.causer',
            'attachments',
            'activitiesAsSubject.causer'
            
        ])
            ->where('ticket_number', $ticket_number)
            ->firstOrFail();
        $this->authorize('view', $this->ticket);
    }

    public function getTitle(): string|Htmlable
    {
        return $this->ticket->ticket_number;
    }
    public function assignAgentAction(): Action
    {
        return AssignAgentAction::make($this->ticket);
    }
    public function manageLabelsAction(): Action
    {
        return ManageLabelsAction::make($this->ticket);
    }
    public function deleteTicketAction(): Action
    {
        return DeleteTicketAction::make($this->ticket);
    }
    public function updateStatusAction(): Action
    {
        return UpdateStatusAction::make($this->ticket);
    }
    public function updatePriorityAction(): Action
    {
        return UpdatePriorityAction::make($this->ticket);
    }

    public function replyForm(Schema $schema): Schema
    {
        return $schema
            ->components(ReplyFormSchema::schema())
            ->statePath('replyData');
    }

    public function submitReply(): void
    {
        $schema = $this->replyForm(Schema::make($this));

        $data = $schema->getState();

        SubmitReplyAction::execute($this->ticket, $data);

        $this->replyData = [
            'is_internal' => 0,
            'content' => null,
            'file_attachments' => []
        ];
    }

    public function closeTicketAction(): Action
    {
        return CloseTicketAction::make($this->ticket);
    }

    public function reopenTicketAction(): Action
    {
        return ReopenTicketAction::make($this->ticket);
    }
}