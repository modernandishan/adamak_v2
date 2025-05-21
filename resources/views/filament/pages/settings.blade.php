<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex justify-center mt-8">
            <x-filament::button type="submit" size="lg" color="primary">
                ذخیره تنظیمات
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
