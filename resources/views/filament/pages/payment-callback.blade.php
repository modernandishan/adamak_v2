<x-filament-panels::page>
    <div class="max-w-2xl mx-auto text-center">
        <div class="mb-8">
            @if($isSuccess)
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-success-100">
                    <x-heroicon-o-check-circle class="w-16 h-16 text-success-600" />
                </div>
            @else
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-danger-100">
                    <x-heroicon-o-x-circle class="w-16 h-16 text-danger-600" />
                </div>
            @endif
        </div>

        <h2 class="text-2xl font-bold mb-4">
            {{ $message }}
        </h2>

        @if($transaction && $isSuccess)
            <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6 mb-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">مبلغ پرداختی:</span>
                        <span class="font-semibold">{{ number_format($transaction->amount) }} تومان</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">موجودی جدید:</span>
                        <span class="font-semibold">{{ number_format($transaction->balance_after) }} تومان</span>
                    </div>
                    @if($transaction->payment_ref_id)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">کد پیگیری:</span>
                            <span class="font-mono">{{ $transaction->payment_ref_id }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="flex gap-4 justify-center">
            <x-filament::button
                :href="route('filament.admin.pages.wallet-charge')"
                tag="a"
                color="gray"
            >
                بازگشت به صفحه شارژ
            </x-filament::button>

            <x-filament::button
                :href="route('filament.admin.resources.transactions.index')"
                tag="a"
            >
                مشاهده تراکنش‌ها
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
