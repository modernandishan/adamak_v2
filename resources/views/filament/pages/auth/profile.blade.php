<x-filament-panels::page>
    {{ $this->form }}

    @if($showVerifyForm)
        <div class="mt-4 p-4 bg-primary-50 dark:bg-primary-950 rounded-lg">
            <h3 class="text-lg font-medium text-primary-700 dark:text-primary-400">تایید شماره موبایل</h3>
            <div class="mt-2">
                <div>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            wire:model="otp"
                            placeholder="کد تایید را وارد کنید"
                        />
                    </x-filament::input.wrapper>
                </div>
                <div class="mt-4 flex space-x-2 space-x-reverse">
                    <x-filament::button wire:click="verifyOtp">
                        تایید
                    </x-filament::button>
                    <x-filament::button color="secondary" wire:click="resendOtp">
                        ارسال مجدد کد
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
