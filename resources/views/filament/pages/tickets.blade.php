<x-filament-panels::page>
    <x-filament::tabs class="mb-4">

        <x-filament::tabs.item wire:click="$set('activeTab', 'all')" :active="$activeTab === 'all'" icon="heroicon-m-list-bullet">
            All
        </x-filament::tabs.item>

        <x-filament::tabs.item wire:click="$set('activeTab', 'active')" :active="$activeTab === 'active'" icon="heroicon-m-sparkles">
            Active
        </x-filament::tabs.item>

        @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
            <x-filament::tabs.item wire:click="$set('activeTab', 'escalated')" :active="$activeTab === 'escalated'"
                icon="heroicon-m-exclamation-triangle" badge-color="warning">
                Escalated
            </x-filament::tabs.item>

            <x-filament::tabs.item wire:click="$set('activeTab', 'my_tickets')" :active="$activeTab === 'my_tickets'"
                icon="heroicon-m-user">
                Assigned to Me
            </x-filament::tabs.item>
        @endif

        <x-filament::tabs.item wire:click="$set('activeTab', 'resolved')" :active="$activeTab === 'resolved'"
            icon="heroicon-m-check-circle">
            Resolved
        </x-filament::tabs.item>

        @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
            <x-filament::tabs.item wire:click="$set('activeTab', 'overdue')" :active="$activeTab === 'overdue'" icon="heroicon-s-fire">
                <span class="text-danger-600 dark:text-danger-400 font-bold">SLA Overdue</span>
            </x-filament::tabs.item>
        @endif

    </x-filament::tabs>

    {{ $this->table }}

</x-filament-panels::page>
