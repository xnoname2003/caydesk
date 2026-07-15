<x-filament-panels::page>
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-6 lg:gap-8 w-full">
        
        {{-- Kolom Utama (Kiri) --}}
        <div class="flex-1 min-w-0 flex flex-col gap-6">
            
            @include('filament.pages.tickets.partials.header')
            
            @include('filament.pages.tickets.partials.comments')
            
            @include('filament.pages.tickets.partials.history')
            
        </div>

        {{-- Kolom Sidebar (Kanan) --}}
        @include('filament.pages.tickets.partials.sidebar')

    </div>
</x-filament-panels::page>