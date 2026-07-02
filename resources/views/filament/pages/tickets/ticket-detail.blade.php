<x-filament-panels::page>
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-6 lg:gap-8 w-full">
        <div class="flex-1 min-w-0 flex flex-col gap-6">
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 sm:p-6 dark:bg-gray-900 dark:border-white/10">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span
                                class="text-xs font-semibold tracking-wider text-slate-500 uppercase">{{ $ticket->ticket_number }}</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                            <span
                                class="text-xs font-medium text-slate-500">{{ $ticket->category->name ?? 'General' }}</span>
                        </div>
                        <h1
                            class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight leading-snug">
                            {{ $ticket->title }}
                        </h1>
                    </div>
                    <div class="shrink-0 flex flex-col items-end gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium bg-blue-50 text-blue-700 border border-blue-100">
                            {{ $ticket->status }}
                        </span>

                        @if (
                            $ticket->due_at &&
                                \Carbon\Carbon::parse($ticket->due_at)->isPast() &&
                                !in_array($ticket->status, ['Resolved', 'Closed']))
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-bold bg-red-100 text-red-800 border border-red-200 shadow-sm animate-pulse">
                                🔥 This ticket is on fire
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-white/10">
                    <img class="w-10 h-10 rounded-full border border-slate-200 shadow-sm object-cover"
                        src="https://ui-avatars.com/api/?name={{ urlencode($ticket->creator->name ?? 'User') }}&background=f1f5f9&color=0f172a"
                        alt="Customer" />
                    @php
                        $creatorRoleName = $ticket->creator->roles->first()->name ?? 'customer';

                        $creatorBadgeColor = match (strtolower($creatorRoleName)) {
                            'administrator', 'admin' => 'bg-purple-100 text-purple-700 border-purple-200',
                            'supervisor' => 'bg-orange-100 text-orange-700 border-orange-200',
                            'agent' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                    @endphp
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $ticket->creator->name ?? 'Unknown' }}
                            </p>
                            <span
                                class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border {{ $creatorBadgeColor }}">
                                {{ $creatorRoleName }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $ticket->creator->email ?? '' }} &bull; Created
                            {{ $ticket->created_at->format('d M, Y H:i') }}
                        </p>
                    </div>
                </div>
                <div
                    class="mt-5 prose prose-slate prose-sm sm:prose-base max-w-none text-slate-700 dark:text-gray-300 whitespace-pre-line">
                    {{ $ticket->description }}
                </div>
                @if ($ticket->attachments->count() > 0)
                    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap gap-2">
                        @foreach ($ticket->attachments as $attachment)
                            <a href="{{ Storage::url($attachment->path) }}" target="_blank"
                                class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600 hover:border-indigo-300">
                                <x-heroicon-o-paper-clip class="w-4 h-4 text-indigo-500" />
                                {{ $attachment->original_name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>


            <div class="space-y-6">
                @foreach ($ticket->comments as $comment)
                    @php
                        $isMe = $comment->user_id === auth()->id();

                        $roleName = $comment->user->roles->first()->name ?? 'customer';

                        $badgeColor = match (strtolower($roleName)) {
                            'administrator', 'admin' => 'bg-purple-100 text-purple-700 border-purple-200',
                            'supervisor' => 'bg-orange-100 text-orange-700 border-orange-200',
                            'agent' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                    @endphp

                    @if ($comment->is_internal)
                        @if (!auth()->user()->hasRole('customer'))
                            <div class="flex gap-4 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                <div class="shrink-0">
                                    <img class="w-10 h-10 rounded-full border border-amber-200 shadow-sm object-cover"
                                        src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name ?? 'User') }}&background=fffbeb&color=b45309"
                                        alt="User" />
                                </div>

                                <div
                                    class="w-full md:max-w-3xl bg-amber-50/80 rounded-2xl shadow-sm border border-amber-200 overflow-hidden relative {{ $isMe ? 'rounded-tr-none' : 'rounded-tl-none' }}">

                                    <div class="absolute top-0 {{ $isMe ? 'left-0' : 'right-0' }}">
                                        <div
                                            class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 border-b border-amber-200 flex items-center gap-1.5 {{ $isMe ? 'rounded-br-lg border-r' : 'rounded-bl-lg border-l' }}">
                                            <x-heroicon-m-lock-closed class="w-3.5 h-3.5" /> Internal Note
                                        </div>
                                    </div>

                                    <div
                                        class="px-5 py-3 border-b border-amber-100/80 bg-amber-100/40 {{ $isMe ? 'pl-32' : 'pr-32' }} flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-amber-900">
                                                {{ $isMe ? 'You' : $comment->user->name ?? 'Unknown' }}
                                            </span>
                                            <span
                                                class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border {{ $badgeColor }}">
                                                {{ $roleName }}
                                            </span>
                                        </div>
                                        <span
                                            class="text-xs text-amber-700">{{ $comment->created_at->format('d M, Y H:i') }}</span>
                                    </div>

                                    <div class="p-5 prose prose-amber prose-sm text-amber-900 whitespace-pre-line">
                                        {{ $comment->content }}
                                    </div>

                                    @if ($comment->attachments->count() > 0)
                                        <div
                                            class="px-5 py-3 border-t border-amber-200/50 bg-amber-100/30 flex flex-wrap gap-3">
                                            @foreach ($comment->attachments as $attachment)
                                                <a href="{{ Storage::url($attachment->path) }}" target="_blank"
                                                    class="flex items-center gap-2 px-3 py-1.5 bg-white border border-amber-200 rounded-lg text-sm text-amber-700 shadow-sm hover:border-amber-400 transition-colors">
                                                    <x-heroicon-o-paper-clip class="w-4 h-4" />
                                                    {{ $attachment->original_name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="flex gap-4 {{ $isMe ? 'flex-row-reverse' : '' }}">
                            <div class="shrink-0">
                                <img class="w-10 h-10 rounded-full border shadow-sm object-cover {{ $isMe ? 'border-indigo-200' : 'border-slate-200' }}"
                                    src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name ?? 'User') }}&background={{ $isMe ? 'e0e7ff' : 'f8fafc' }}&color={{ $isMe ? '3730a3' : '334155' }}"
                                    alt="User" />
                            </div>

                            <div
                                class="w-full md:max-w-3xl rounded-2xl shadow-sm border overflow-hidden {{ $isMe ? 'bg-indigo-50 border-indigo-100 rounded-tr-none' : 'bg-white border-slate-200 rounded-tl-none' }}">

                                <div
                                    class="px-5 py-3 border-b flex flex-wrap items-center justify-between gap-2 {{ $isMe ? 'border-indigo-100 bg-indigo-100/30' : 'border-slate-100 bg-slate-50/50' }}">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-sm font-semibold {{ $isMe ? 'text-indigo-900' : 'text-slate-900' }}">
                                            {{ $isMe ? 'You' : $comment->user->name ?? 'Unknown' }}
                                        </span>
                                        <span
                                            class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border {{ $badgeColor }}">
                                            {{ $roleName }}
                                        </span>
                                    </div>
                                    <span class="text-xs {{ $isMe ? 'text-indigo-700' : 'text-slate-500' }}">
                                        {{ $comment->created_at->format('d M, Y H:i') }}
                                    </span>
                                </div>

                                <div
                                    class="p-5 prose prose-sm whitespace-pre-line {{ $isMe ? 'prose-indigo text-indigo-900' : 'prose-slate text-slate-700' }}">
                                    {{ $comment->content }}
                                </div>
                                @if ($comment->attachments->count() > 0)
                                    <div
                                        class="px-5 py-3 border-t flex flex-wrap gap-3 {{ $isMe ? 'border-indigo-100 bg-indigo-50/50' : 'border-slate-100 bg-slate-50' }}">
                                        @foreach ($comment->attachments as $attachment)
                                            <a href="{{ Storage::url($attachment->path) }}" target="_blank"
                                                class="flex items-center gap-2 px-3 py-1.5 bg-white border rounded-lg text-sm shadow-sm transition-colors {{ $isMe ? 'border-indigo-200 text-indigo-700 hover:border-indigo-400' : 'border-slate-200 text-slate-600 hover:border-indigo-300' }}">
                                                <x-heroicon-o-paper-clip
                                                    class="w-4 h-4 {{ $isMe ? 'text-indigo-500' : 'text-slate-400' }}" />
                                                {{ $attachment->original_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Leave a Reply</h3>

                <form wire:submit="submitReply">
                    {{ $this->replyForm(\Filament\Schemas\Schema::make($this)) }}

                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            <x-heroicon-m-paper-airplane class="w-4 h-4 mr-2" /> Send Reply
                        </button>
                    </div>
                </form>
            </div>


        </div>

        <div class="w-full lg:w-80 shrink-0 space-y-6">
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden dark:bg-gray-900 dark:border-white/10">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 dark:bg-gray-800/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Ticket
                        Properties</h3>
                </div>

                <div class="p-5 space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Due Date
                            (SLA)</label>
                        <div
                            class="px-3 py-2 text-sm border border-slate-300 rounded-lg bg-slate-50 text-slate-700 font-medium flex items-center justify-between">
                            <span>{{ $ticket->due_at ? \Carbon\Carbon::parse($ticket->due_at)->format('d M, Y H:i') : '-' }}</span>

                            @if (
                                $ticket->due_at &&
                                    \Carbon\Carbon::parse($ticket->due_at)->isPast() &&
                                    !in_array($ticket->status, ['Resolved', 'Closed']))
                                <x-heroicon-s-exclamation-circle class="w-4 h-4 text-red-500" />
                            @endif
                        </div>
                    </div>

                    @if (!auth()->user()->hasRole('customer'))
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Response Target
                                (SLA)</label>
                            <div
                                class="px-3 py-2 text-sm border border-slate-300 rounded-lg bg-slate-50 text-slate-700 font-medium">
                                @if ($ticket->first_responded_at)
                                    <span class="text-emerald-600 flex items-center gap-1.5">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                        Responded at
                                        {{ \Carbon\Carbon::parse($ticket->first_responded_at)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="flex items-center justify-between">
                                        {{ $ticket->response_due_at ? \Carbon\Carbon::parse($ticket->response_due_at)->format('d M, Y H:i') : '-' }}

                                        @if (
                                            $ticket->response_due_at &&
                                                \Carbon\Carbon::parse($ticket->response_due_at)->isPast() &&
                                                !in_array($ticket->status, ['Resolved', 'Closed']))
                                            <span
                                                class="flex items-center gap-1 text-red-600 text-xs bg-red-100 px-2 py-0.5 rounded animate-pulse">
                                                <x-heroicon-s-fire class="w-3 h-3" /> Late
                                            </span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Status</label>
                        <div
                            class="flex items-center justify-between px-3 py-2 border border-slate-300 rounded-lg bg-slate-50">
                            <span class="text-sm text-slate-700 font-medium">
                                {{ $ticket->status }}
                            </span>

                            @php
                                $statusService = app(\App\Services\TicketStatusService::class);
                                $statusOptions = $statusService->getAllowedNextStatuses($ticket->status);
                                $canUpdateStatus = false;

                                $user = auth()->user();
                                $isAgent = $user->hasRole('agent');
                                $isAdminOrSpv = $user->hasAnyRole([
                                    'administrator',
                                    'supervisor',
                                ]);

                                if ($isAgent) {
                                    if ($ticket->assigned_agent_id === $user->id) {
                                        $agentAllowed = [
                                            \App\Services\TicketStatusService::STATUS_IN_PROGRESS,
                                            \App\Services\TicketStatusService::STATUS_WAITING_FOR_CUSTOMER,
                                            \App\Services\TicketStatusService::STATUS_RESOLVED,
                                            \App\Services\TicketStatusService::STATUS_ESCALATED,
                                        ];

                                        $validForAgent = array_intersect(array_values($statusOptions), $agentAllowed);
                                        $canUpdateStatus = count($validForAgent) > 0;
                                    }
                                } elseif ($isAdminOrSpv) {
                                    $canUpdateStatus = count($statusOptions) > 0;
                                }
                            @endphp

                            @if ($canUpdateStatus)
                                {{ $this->updateStatusAction }}
                            @endif
                        </div>
                    </div>

                    @if (auth()->user()->hasRole('customer'))
                        @php
                            $statusService = app(\App\Services\TicketStatusService::class);
                            $canClose =
                                $ticket->status !== \App\Services\TicketStatusService::STATUS_CLOSED &&
                                $statusService->isValidTransition(
                                    $ticket->status,
                                    \App\Services\TicketStatusService::STATUS_CLOSED,
                                );

                            $canReopen =
                                $ticket->status !== \App\Services\TicketStatusService::STATUS_REOPENED &&
                                $statusService->isValidTransition(
                                    $ticket->status,
                                    \App\Services\TicketStatusService::STATUS_REOPENED,
                                );
                        @endphp

                        @if ($canClose || $canReopen)
                            <div class="pt-2">
                                <div class="flex flex-col gap-2">
                                    @if ($canClose)
                                        {{ $this->closeTicketAction }}
                                    @endif

                                    @if ($canReopen)
                                        {{ $this->reopenTicketAction }}
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Priority</label>
                        <div
                            class="flex items-center justify-between px-3 py-2 border border-slate-300 rounded-lg bg-slate-50">
                            <span class="text-sm text-slate-700 font-medium">
                                {{ $ticket->priority->name ?? '-' }}
                            </span>
                            @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
                                {{ $this->updatePriorityAction }}
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Assignee</label>
                        <div
                            class="flex items-center justify-between px-3 py-2 border border-slate-300 rounded-lg bg-slate-50">
                            <span class="text-sm text-slate-700 font-medium">
                                {{ $ticket->assignedAgent->name ?? 'Unassigned' }}
                            </span>
                            @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
                                {{ $this->assignAgentAction }}
                            @endif
                        </div>
                    </div>
                </div>

                @if (auth()->user()->hasRole('administrator'))
                    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                        <div class="w-full flex justify-center">
                            {{ $this->deleteTicketAction }}
                        </div>
                    </div>
                @endif
            </div>

            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden p-5 dark:bg-gray-900 dark:border-white/10">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tags</h3>
                    @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
                        {{ $this->manageTagsAction }}
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    @forelse($ticket->labels ?? [] as $label)
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $label->name }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 italic">No tags</span>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
