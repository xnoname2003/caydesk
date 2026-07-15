<div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-5 dark:bg-slate-900 dark:border-slate-700">
    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
        <x-heroicon-m-clock class="w-5 h-5 text-slate-400 dark:text-slate-500" />
        Ticket History
    </h3>
    <div class="relative border-l border-slate-200 dark:border-slate-700 ml-3 space-y-6 pb-4" wire:poll.3s.visible>
        @php
            $allActivities = $ticket
                ->activitiesAsSubject()
                ->get()
                ->concat($ticket->comments()->with('activitiesAsSubject')->get()->flatMap->activitiesAsSubject)
                ->sortByDesc('created_at');
        @endphp

        @forelse ($allActivities as $activity)
            @php
                $isInternalLog = false;

                if (str_ends_with($activity->subject_type ?? '', 'Comment')) {
                    if ($activity->subject && $activity->subject->is_internal) {
                        $isInternalLog = true;
                    } else {
                        $tempChanges = $activity->attribute_changes ?? [];
                        $tempAttrs = $tempChanges['attributes'] ?? [];
                        if (isset($tempAttrs['is_internal']) && $tempAttrs['is_internal'] == 1) {
                            $isInternalLog = true;
                        }
                    }
                }
            @endphp

            @if (! ($isInternalLog && auth()->user()->hasRole('customer')))
                @php
                    $changes = $activity->attribute_changes ?? [];
                    $attributes = $changes['attributes'] ?? [];
                    $oldValues = $changes['old'] ?? [];
                    $realChanges = [];
                    if (is_array($attributes)) {
                        foreach ($attributes as $key => $newValue) {
                            if (!isset($oldValues[$key]) || $oldValues[$key] != $newValue) {
                                $realChanges[$key] = $newValue;
                            }
                        }
                    }
                    $isCreateEvent = $activity->event === 'created' || (!empty($attributes) && empty($oldValues));
                    $isLabelUpdate =
                        $activity->description === 'Labels have been updated' ||
                        array_key_exists('labels', $realChanges);
                @endphp

                <div class="relative pl-6" wire:key="history-{{ $activity->id }}">
                    <span
                        class="absolute -left-1.25 top-1.5 w-2.5 h-2.5 rounded-full bg-slate-300 dark:bg-slate-600 ring-4 ring-white dark:ring-slate-900"></span>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-1 mb-1">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-200">
                            {{ $activity->causer ? $activity->causer->name : 'System' }}
                        </p>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            {{ $activity->created_at->format('d M, Y H:i') }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">
                        {{ $activity->description }}
                    </p>

                    @if ($isLabelUpdate && !auth()->user()->hasRole('customer'))
                        <div
                            class="mt-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-3 text-xs border border-indigo-100 dark:border-indigo-800/50">
                            <div class="flex items-center gap-2 mb-2">
                                <x-heroicon-m-tag class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                                <span class="text-indigo-700 dark:text-indigo-300 font-medium">Label Changes</span>
                            </div>
                            <div
                                class="grid grid-cols-[100px_1fr_1fr] gap-2 py-1 border-t border-indigo-100 dark:border-indigo-800/50 pt-2 items-center">
                                <span class="font-medium text-indigo-500">Labels</span>
                                <div class="flex flex-wrap gap-1">
                                    @php $oldLabels = is_array($oldValues['labels'] ?? null) ? $oldValues['labels'] : json_decode($oldValues['labels'] ?? '[]', true); @endphp
                                    @forelse(is_array($oldLabels) ? $oldLabels : [] as $oldLabel)
                                        <span
                                            class="text-red-500 dark:text-red-400 line-through bg-red-100 dark:bg-red-900/30 px-1.5 py-0.5 rounded">{{ $oldLabel['name'] ?? $oldLabel }}</span>
                                    @empty
                                        <span class="text-slate-400 italic">None</span>
                                    @endforelse
                                </div>
                                <div class="flex flex-wrap gap-1 items-center">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium mr-1">&rarr;</span>
                                    @php $newLabels = is_array($realChanges['labels'] ?? null) ? $realChanges['labels'] : json_decode($realChanges['labels'] ?? '[]', true); @endphp
                                    @forelse(is_array($newLabels) ? $newLabels : [] as $newLabel)
                                        <span
                                            class="text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded">{{ $newLabel['name'] ?? $newLabel }}</span>
                                    @empty
                                        <span class="text-slate-400 italic">None</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @php unset($realChanges['labels']); @endphp
                    @endif

                    @if ((count($realChanges) > 0 || $isCreateEvent) && !auth()->user()->hasRole('customer'))
                        <div
                            class="mt-2 bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3 text-xs border border-slate-100 dark:border-slate-700/50">
                            @if (count($realChanges) > 0)
                                @foreach ($realChanges as $key => $newValue)
                                    <div
                                        class="grid grid-cols-[140px_1fr_1fr] sm:grid-cols-[160px_1fr_1fr] gap-2 py-1 border-b border-slate-200 dark:border-slate-700 last:border-0 last:pb-0 items-center">

                                        <span
                                            class="font-medium text-slate-500 dark:text-slate-400 capitalize wrap-break-word pr-2">
                                            {{ str_replace(['.', '_id', '_'], [' ', '', ' '], $key) }}
                                        </span>

                                        <span class="text-red-500 dark:text-red-400 line-through truncate">
                                            @if ($key === 'is_internal')
                                                {{ isset($oldValues[$key]) ? ($oldValues[$key] ? 'True' : 'False') : '-' }}
                                            @else
                                                {{ is_array($oldValues[$key] ?? null) ? json_encode($oldValues[$key]) : $oldValues[$key] ?? '-' }}
                                            @endif
                                        </span>

                                        <span
                                            class="text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1 overflow-hidden">
                                            <span class="shrink-0">&rarr;</span>
                                            <span class="truncate">
                                                @if ($key === 'is_internal')
                                                    {{ $newValue ? 'True' : 'False' }}
                                                @else
                                                    {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                                @endif
                                            </span>
                                        </span>

                                    </div>
                                @endforeach
                            @else
                                <span class="text-slate-500 dark:text-slate-400 italic">Record created.</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400 pl-6 italic">No activity recorded yet.</p>
        @endforelse
    </div>
</div>
