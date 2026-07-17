<x-filament-panels::page>
    <x-filament::tabs class="mb-4">

        <x-filament::tabs.item wire:click="$set('activeTab', 'all')" :active="$activeTab === 'all'" icon="heroicon-m-list-bullet" :badge="$this->getBadgeCount('all')">
            All
        </x-filament::tabs.item>

        <x-filament::tabs.item wire:click="$set('activeTab', 'active')" :active="$activeTab === 'active'" icon="heroicon-m-sparkles" :badge="$this->getBadgeCount('active')">
            Active
        </x-filament::tabs.item>

        @if (!auth()->user()->hasRole('customer'))
            <x-filament::tabs.item wire:click="$set('activeTab', 'assigned_to_me')" :active="in_array($activeTab, ['assigned_to_me', 'my_tickets'])"
                icon="heroicon-m-user" :badge="$this->getBadgeCount('assigned_to_me')">
                Assigned to Me
            </x-filament::tabs.item>

            <x-filament::tabs.item wire:click="$set('activeTab', 'created_by_me')" :active="$activeTab === 'created_by_me'"
                icon="heroicon-m-pencil-square" :badge="$this->getBadgeCount('created_by_me')">
                Created by Me
            </x-filament::tabs.item>
        @endif

        @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
            <x-filament::tabs.item wire:click="$set('activeTab', 'escalated')" :active="$activeTab === 'escalated'"
                icon="heroicon-m-exclamation-triangle" :badge="$this->getBadgeCount('escalated')">
                Escalated
            </x-filament::tabs.item>
        @endif

        <x-filament::tabs.item wire:click="$set('activeTab', 'resolved')" :active="$activeTab === 'resolved'"
            icon="heroicon-m-check-circle" :badge="$this->getBadgeCount('resolved')">
            Resolved
        </x-filament::tabs.item>

        @if (auth()->user()->hasAnyRole(['administrator', 'supervisor']))
            <x-filament::tabs.item wire:click="$set('activeTab', 'overdue')" :active="$activeTab === 'overdue'" icon="heroicon-s-fire" :badge="$this->getBadgeCount('overdue')">
                <span class="text-danger-600 dark:text-danger-400 font-bold">SLA Overdue</span>
            </x-filament::tabs.item>
        @endif

    </x-filament::tabs>

    {{ $this->table }}

</x-filament-panels::page>