<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <form wire:submit="submit">
            {{ $this->form }}

            <div class="flex justify-center mt-8">
                <x-filament::button
                    type="submit"
                    size="lg"
                    color="success"
                    icon="heroicon-m-credit-card"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>پرداخت و شارژ کیف پول</span>
                    <span wire:loading>در حال پردازش...</span>
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
