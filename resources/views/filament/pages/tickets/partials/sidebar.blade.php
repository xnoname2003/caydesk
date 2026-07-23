<div class="w-full lg:w-80 shrink-0 space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden dark:bg-slate-900 dark:border-slate-700">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Ticket Properties</h3>
        </div>

        <div class="p-5 space-y-5">
            <div wire:poll.3s.visible>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Due Date (SLA)</label>
                <div class="px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 font-medium flex items-center justify-between">
                    <span>{{ $ticket->due_at ? \Carbon\Carbon::parse($ticket->due_at)->format('d M, Y H:i') : '-' }}</span>
                    @if ($ticket->due_at && \Carbon\Carbon::parse($ticket->due_at)->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']))
                        <x-heroicon-s-exclamation-circle class="w-4 h-4 text-red-500" />
                    @endif
                </div>
            </div>

            @if (!auth()->user()->hasRole('customer'))
                <div wire:poll.3s.visible>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Response Target (SLA)</label>
                    <div class="px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 font-medium">
                        @if ($ticket->first_responded_at)
                            <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                <x-heroicon-s-check-circle class="w-4 h-4" /> Responded at {{ \Carbon\Carbon::parse($ticket->first_responded_at)->format('H:i') }}
                            </span>
                        @else
                            <span class="flex items-center justify-between">
                                {{ $ticket->response_due_at ? \Carbon\Carbon::parse($ticket->response_due_at)->format('d M, Y H:i') : '-' }}
                                @if ($ticket->response_due_at && \Carbon\Carbon::parse($ticket->response_due_at)->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']))
                                    <span class="flex items-center gap-1 text-red-600 dark:text-red-400 text-xs bg-red-100 dark:bg-red-900/30 px-2 py-0.5 rounded animate-pulse">
                                        <x-heroicon-s-fire class="w-3 h-3" /> Late
                                    </span>
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Status</label>
                <div class="flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium" wire:poll.3s.visible>
                        {{ $ticket->status }}
                    </span>
                    @php
                        $statusOptions = \App\Services\TicketStatusService::getAllowedNextStatuses($ticket->status);
                        $canUpdateStatus = false;
                        $user = auth()->user();
                        $isAgent = $user->hasRole('agent');
                        $isAdminOrSpv = $user->hasAnyRole(['administrator', 'supervisor']);
                        if ($isAdminOrSpv) {
                            $canUpdateStatus = count($statusOptions) > 0;
                        } elseif ($isAgent && $ticket->assigned_agent_id === $user->id) {
                            $canUpdateStatus = count($statusOptions) > 0;
                        }
                    @endphp
                    @if ($canUpdateStatus)
                        {{ $this->updateStatusAction }}
                    @endif
                </div>
            </div>

            @if (auth()->id() === $ticket->created_by && !auth()->user()->hasAnyRole(['administrator', 'supervisor']))
                @php
                    $statusService = app(\App\Services\TicketStatusService::class);
                    $canClose = $ticket->status !== \App\Services\TicketStatusService::STATUS_CLOSED && $statusService->isValidTransition($ticket->status, \App\Services\TicketStatusService::STATUS_CLOSED);
                    $canReopen = $ticket->status !== \App\Services\TicketStatusService::STATUS_REOPENED && $statusService->isValidTransition($ticket->status, \App\Services\TicketStatusService::STATUS_REOPENED);
                @endphp
                @if ($canClose || $canReopen)
                    <div class="pt-2">
                        <div class="flex flex-col gap-2">
                            @if ($canClose) {{ $this->closeTicketAction }} @endif
                            @if ($canReopen) {{ $this->reopenTicketAction }} @endif
                        </div>
                    </div>
                @endif
            @endif

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Priority</label>
                <div class="flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium" wire:poll.3s.visible>
                        {{ $ticket->priority->name ?? '-' }}
                    </span>
                    @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
                        {{ $this->updatePriorityAction }}
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Assignee</label>
                <div class="flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium" wire:poll.3s.visible>
                        {{ $ticket->assignedAgent->name ?? 'Unassigned' }}
                    </span>
                    @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
                        {{ $this->assignAgentAction }}
                    @endif
                </div>
            </div>
        </div>

        @if (auth()->user()->hasRole('administrator'))
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <div class="w-full flex justify-center">
                    {{ $this->deleteTicketAction }}
                </div>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden p-5 dark:bg-slate-900 dark:border-slate-700">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Labels</h3>
            @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
                {{ $this->manageLabelsAction }}
            @endif
        </div>
        <div class="flex flex-wrap gap-2" wire:poll.3s.visible>
            @forelse($ticket->labels ?? [] as $label)
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600">
                    {{ $label->name }}
                </span>
            @empty
                <span class="text-xs text-slate-400 italic">No Labels</span>
            @endforelse
        </div>
    </div>
</div>