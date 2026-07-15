<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 dark:bg-slate-900 dark:border-slate-700">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4 mb-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-2 flex-wrap">
                <span class="text-xs font-semibold tracking-wider text-slate-500 dark:text-slate-400 uppercase">{{ $ticket->ticket_number }}</span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 truncate">{{ $ticket->category->name ?? 'General' }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight leading-snug wrap-break-word">
                {{ $ticket->title }}
            </h1>
        </div>
        <div class="shrink-0 flex flex-row sm:flex-col items-center sm:items-end gap-2 mt-1 sm:mt-0 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800">
                {{ $ticket->status }}
            </span>

            @if ($ticket->due_at && \Carbon\Carbon::parse($ticket->due_at)->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']))
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-bold bg-red-100 text-red-800 border border-red-200 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 shadow-sm animate-pulse">
                    🔥 <span class="hidden sm:inline">This ticket is on fire</span><span class="sm:hidden">On fire</span>
                </span>
            @endif
        </div>
    </div>

    <div class="flex items-start sm:items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-700/60">
        <img class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-700 shadow-sm object-cover shrink-0 mt-1 sm:mt-0"
            src="https://ui-avatars.com/api/?name={{ urlencode($ticket->creator->name ?? 'User') }}&background=f1f5f9&color=0f172a"
            alt="Customer" />
        @php
            $creatorRoleName = $ticket->creator->roles->first()->name ?? 'customer';
            $creatorBadgeColor = match (strtolower($creatorRoleName)) {
                'administrator', 'admin' => 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800',
                'supervisor' => 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800',
                'agent' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
                default => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
            };
        @endphp
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate max-w-full">
                    {{ $ticket->creator->name ?? 'Unknown' }}
                </p>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border shrink-0 {{ $creatorBadgeColor }}">
                    {{ $creatorRoleName }}
                </span>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-x-1 gap-y-0.5">
                <span class="truncate max-w-full">{{ $ticket->creator->email ?? '' }}</span>
                <span class="hidden sm:inline">&bull;</span>
                <span class="w-full sm:w-auto">Created {{ $ticket->created_at->format('d M, Y H:i') }}</span>
            </div>
        </div>
    </div>
    <div class="mt-5 prose prose-slate prose-sm sm:prose-base max-w-none text-slate-700 dark:text-slate-300 whitespace-pre-line wrap-break-word">
        {{ $ticket->description }}
    </div>
    @if ($ticket->attachments->count() > 0)
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60 flex flex-wrap gap-2">
            @foreach ($ticket->attachments as $attachment)
                <a href="{{ Storage::url($attachment->path) }}" target="_blank"
                    class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:border-indigo-300 dark:hover:border-indigo-500 max-w-full">
                    <x-heroicon-o-paper-clip class="w-4 h-4 text-indigo-500 dark:text-indigo-400 shrink-0" />
                    <span class="truncate">{{ $attachment->original_name }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>