<x-filament-panels::page>
    <form wire:submit="createManualReferral">
        {{ $this->form }}
        
        <div class="mt-6 flex justify-end">
            <x-filament::button
                type="submit"
                color="success"
            >
                <x-filament::icon
                    icon="heroicon-o-check"
                    class="h-5 w-5 me-1"
                />
                Create Referral & Award Coins
            </x-filament::button>
        </div>
    </form>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
