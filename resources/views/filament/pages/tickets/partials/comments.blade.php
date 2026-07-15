<div class="space-y-6" wire:poll.3s.visible>
    @foreach ($ticket->comments as $comment)
        @php
            $isMe = $comment->user_id === auth()->id();
            $roleName = $comment->user->roles->first()->name ?? 'customer';
            $badgeColor = match (strtolower($roleName)) {
                'administrator', 'admin' => 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800',
                'supervisor' => 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800',
                'agent' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
                default => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
            };
        @endphp

        @if ($comment->is_internal)
            @if (!auth()->user()->hasRole('customer'))
                <div class="flex gap-4 {{ $isMe ? 'flex-row-reverse' : '' }}">
                    <div class="shrink-0">
                        <img class="w-10 h-10 rounded-full border border-amber-200 dark:border-amber-800 shadow-sm object-cover"
                            src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name ?? 'User') }}&background=fffbeb&color=b45309" alt="User" />
                    </div>
                    <div class="w-full md:max-w-3xl bg-amber-50/80 dark:bg-amber-900/20 rounded-2xl shadow-sm border border-amber-200 dark:border-amber-800/50 overflow-hidden relative {{ $isMe ? 'rounded-tr-none' : 'rounded-tl-none' }}">
                        <div class="absolute top-0 {{ $isMe ? 'left-0' : 'right-0' }}">
                            <div class="bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-400 text-xs font-bold px-3 py-1 border-b border-amber-200 dark:border-amber-800/50 flex items-center gap-1.5 {{ $isMe ? 'rounded-br-lg border-r' : 'rounded-bl-lg border-l' }}">
                                <x-heroicon-m-lock-closed class="w-3.5 h-3.5" /> Internal Note
                            </div>
                        </div>
                        <div class="px-5 py-3 border-b border-amber-100/80 dark:border-amber-800/30 bg-amber-100/40 dark:bg-amber-900/30 {{ $isMe ? 'pl-32' : 'pr-32' }} flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-amber-900 dark:text-amber-300">
                                    {{ $isMe ? 'You' : $comment->user->name ?? 'Unknown' }}
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border {{ $badgeColor }}">
                                    {{ $roleName }}
                                </span>
                            </div>
                            <span class="text-xs text-amber-700 dark:text-amber-500/80">{{ $comment->created_at->format('d M, Y H:i') }}</span>
                        </div>
                        <div class="p-5 prose prose-amber prose-sm text-amber-900 dark:text-amber-200 whitespace-pre-line">
                            {{ $comment->content }}
                        </div>
                        @if ($comment->attachments->count() > 0)
                            <div class="px-5 py-3 border-t border-amber-200/50 dark:border-amber-800/50 bg-amber-100/30 dark:bg-amber-900/20 flex flex-wrap gap-3">
                                @foreach ($comment->attachments as $attachment)
                                    <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800/60 rounded-lg text-sm text-amber-700 dark:text-amber-400 shadow-sm hover:border-amber-400 transition-colors">
                                        <x-heroicon-o-paper-clip class="w-4 h-4" /> {{ $attachment->original_name }}
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
                    <img class="w-10 h-10 rounded-full border shadow-sm object-cover {{ $isMe ? 'border-indigo-200 dark:border-indigo-800' : 'border-slate-200 dark:border-slate-700' }}"
                        src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name ?? 'User') }}&background={{ $isMe ? 'e0e7ff' : 'f8fafc' }}&color={{ $isMe ? '3730a3' : '334155' }}" alt="User" />
                </div>
                <div class="w-full md:max-w-3xl rounded-2xl shadow-sm border overflow-hidden {{ $isMe ? 'bg-indigo-50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-800/50 rounded-tr-none' : 'bg-white dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 rounded-tl-none' }}">
                    <div class="px-5 py-3 border-b flex flex-wrap items-center justify-between gap-2 {{ $isMe ? 'border-indigo-100 dark:border-indigo-800/50 bg-indigo-100/30 dark:bg-indigo-900/30' : 'border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold {{ $isMe ? 'text-indigo-900 dark:text-indigo-300' : 'text-slate-900 dark:text-slate-300' }}">
                                {{ $isMe ? 'You' : $comment->user->name ?? 'Unknown' }}
                            </span>
                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border {{ $badgeColor }}">
                                {{ $roleName }}
                            </span>
                        </div>
                        <span class="text-xs {{ $isMe ? 'text-indigo-700 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400' }}">
                            {{ $comment->created_at->format('d M, Y H:i') }}
                        </span>
                    </div>
                    <div class="p-5 prose prose-sm whitespace-pre-line {{ $isMe ? 'prose-indigo text-indigo-900 dark:text-indigo-200' : 'prose-slate text-slate-700 dark:text-slate-300' }}">
                        {{ $comment->content }}
                    </div>
                    @if ($comment->attachments->count() > 0)
                        <div class="px-5 py-3 border-t flex flex-wrap gap-3 {{ $isMe ? 'border-indigo-100 dark:border-indigo-800/50 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/80' }}">
                            @foreach ($comment->attachments as $attachment)
                                <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-transparent border rounded-lg text-sm shadow-sm transition-colors {{ $isMe ? 'border-indigo-200 dark:border-indigo-700 text-indigo-700 dark:text-indigo-400 hover:border-indigo-400' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:border-indigo-300' }}">
                                    <x-heroicon-o-paper-clip class="w-4 h-4 {{ $isMe ? 'text-indigo-500' : 'text-slate-400' }}" />
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

<div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Leave a Reply</h3>
    <form wire:submit="submitReply" wire:key="reply-form-{{ $formKey }}">
        {{ $this->replyForm(\Filament\Schemas\Schema::make($this)) }}
        <div class="mt-4 flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <x-heroicon-m-paper-airplane class="w-4 h-4 mr-2" /> Send Reply
            </button>
        </div>
    </form>
</div>