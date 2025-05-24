<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">موجودی کیف پول</h2>
                <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mt-2">
                    {{ $this->getWalletBalance() }} تومان
                </p>
            </div>

            <div class="flex gap-2">
                <x-filament::button
                    :href="route('filament.admin.pages.wallet-charge')"
                    tag="a"
                    icon="heroicon-o-plus-circle"
                    size="sm"
                >
                    شارژ کیف پول
                </x-filament::button>

                <x-filament::button
                    :href="route('filament.admin.resources.transactions.index')"
                    tag="a"
                    color="gray"
                    icon="heroicon-o-list-bullet"
                    size="sm"
                >
                    تراکنش‌ها
                </x-filament::button>
            </div>
        </div>

        @php
            $transactions = $this->getLastTransactions();
        @endphp

        @if(count($transactions) > 0)
            <div class="mt-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">
                    آخرین تراکنش‌ها
                </h3>
                <div class="space-y-2">
                    @foreach($transactions as $transaction)
                        <div class="flex items-center justify-between py-2 border-b dark:border-gray-700 last:border-0">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium">{{ $transaction['type'] }}</span>
                                <x-filament::badge :color="$transaction['status_color']" size="sm">
                                    {{ $transaction['status'] }}
                                </x-filament::badge>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">{{ $transaction['amount'] }} تومان</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction['date'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
