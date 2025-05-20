<x-filament-panels::page>
    <p class="text-amber">
        <a class="danger:text-red" href="{{url('/adamak')}}">
            >>
            بازگشت به ادمک
        </a>
    </p>
    <!-- نمایش موجودی کیف پول -->
    <div class="mb-6">
        <div style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);" class="rounded-2xl p-6 text-white shadow-lg border border-blue-200 dark:border-blue-900">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-white mb-1" style="color: white !important;">موجودی کیف پول</h3>
                    <p class="text-3xl font-bold text-white" style="color: white !important;">{{ $this->getFormattedWalletBalance() }}</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full">
                    <svg class="h-8 w-8 text-white" style="color: white !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-white text-sm" style="color: white !important;">
                <svg class="h-4 w-4 ml-1" style="color: white !important;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                موجودی قابل استفاده
            </div>
        </div>
    </div>

    <!-- فرم پروفایل -->
    <form wire:submit="save">
        {{ $this->form }}

        <!-- دکمه ذخیره -->
        <div class="mt-6 flex justify-center">
            <x-filament::button type="submit" size="lg" color="primary" icon="heroicon-o-check">
                ذخیره اطلاعات
            </x-filament::button>
        </div>
    </form>

    @if($showVerifyForm)
        <div class="mt-6 p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    تایید شماره موبایل
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    کد تایید به شماره {{ auth()->user()->mobile }} ارسال شد
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <x-filament::input
                        type="text"
                        wire:model="otp"
                        placeholder="کد تایید را وارد کنید"
                        autocomplete="one-time-code"
                        inputmode="numeric"
                        maxlength="6"
                        class="block w-full"
                    />
                    @error('otp')
                    <div class="text-sm text-red-600 dark:text-red-400 mt-1">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <!-- نمایش اطلاعات محدودیت -->
                @if($resendAttempts > 0)
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            تعداد ارسال: {{ $resendAttempts }} از {{ $maxAttempts }}
                            @if($this->getRemainingAttempts() > 0)
                                ({{ $this->getRemainingAttempts() }} بار دیگر می‌توانید تلاش کنید)
                            @endif
                        </p>
                    </div>
                @endif

                <div class="flex flex-wrap gap-3">
                    <x-filament::button
                        wire:click="verifyOtp"
                        color="primary"
                        icon="heroicon-o-check"
                    >
                        تایید
                    </x-filament::button>

                    <x-filament::button
                        wire:click="resendOtp"
                        color="gray"
                        icon="heroicon-o-arrow-path"
                        :disabled="!$this->getCanResend()"
                        wire:poll.1s="updateCooldownTime"
                    >
                        @if($this->getRemainingCooldownTime() > 0)
                            ارسال مجدد کد ({{ $this->getRemainingCooldownTime() }}s)
                        @elseif($resendAttempts >= $maxAttempts)
                            محدودیت تلاش
                        @else
                            ارسال مجدد کد
                        @endif
                    </x-filament::button>

                    <x-filament::button
                        wire:click="showVerifyForm = false"
                        color="danger"
                        icon="heroicon-o-x-mark"
                    >
                        لغو
                    </x-filament::button>
                </div>

                <!-- نمایش هشدار در صورت رسیدن به محدودیت -->
                @if($resendAttempts >= $maxAttempts)
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a1 1 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    محدودیت تلاش
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>شما به حداکثر تعداد تلاش رسیده‌اید. لطفاً بعداً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- JavaScript برای مدیریت timer -->
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('startCooldownTimer', () => {
                    // Timer در سمت کلاینت برای بهتر بودن UX اما اصل کار در سرور است
                    console.log('Cooldown timer started');
                });
            });
        </script>
    @endif
</x-filament-panels::page>
