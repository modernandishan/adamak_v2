<x-filament-panels::page>
    <div class="max-w-4xl mx-auto">
        {{-- Header Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            {{-- Featured Image --}}
            @if($test->featured_image)
                <div class="aspect-w-16 aspect-h-6">
                    <img
                        src="{{ Storage::url($test->featured_image) }}"
                        alt="{{ $test->title }}"
                        class="w-full h-64 object-cover"
                    >
                </div>
            @else
                <div class="w-full h-64 bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                    <x-heroicon-o-academic-cap class="w-32 h-32 text-white opacity-50" />
                </div>
            @endif

            <div class="p-8">
                {{-- Category & Badges --}}
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $test->category->title }}
                    </span>
                    @if($test->is_multi_stage)
                        <span class="text-xs bg-info-100 text-info-700 dark:bg-info-900 dark:text-info-300 px-3 py-1 rounded-full">
                            چند مرحله‌ای
                        </span>
                    @endif
                    @if($test->requires_family)
                        <span class="text-xs bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300 px-3 py-1 rounded-full">
                            نیاز به ثبت خانواده
                        </span>
                    @endif
                    @if($hasPurchased)
                        <span class="text-xs bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300 px-3 py-1 rounded-full">
                            خریداری شده
                        </span>
                    @endif
                </div>

                {{-- Title --}}
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ $test->title }}
                </h1>

                {{-- Short Description --}}
                @if($test->short_description)
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                        {{ $test->short_description }}
                    </p>
                @endif

                {{-- Meta Information --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                        <x-heroicon-m-question-mark-circle class="w-8 h-8 mx-auto text-primary-500 mb-2" />
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $test->questions_count }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            سوال
                        </div>
                    </div>

                    @if($test->estimated_completion_time)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                            <x-heroicon-m-clock class="w-8 h-8 mx-auto text-primary-500 mb-2" />
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $test->estimated_completion_time }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                دقیقه
                            </div>
                        </div>
                    @endif

                    @if($test->is_multi_stage)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                            <x-heroicon-m-squares-2x2 class="w-8 h-8 mx-auto text-primary-500 mb-2" />
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $test->stages_count }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                مرحله
                            </div>
                        </div>
                    @endif

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                        <x-heroicon-m-currency-dollar class="w-8 h-8 mx-auto text-primary-500 mb-2" />
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($test->effective_price) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            تومان
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description Section --}}
        @if($test->description)
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    توضیحات آزمون
                </h2>
                <div class="prose prose-lg dark:prose-invert max-w-none">
                    {!! $test->description !!}
                </div>
            </div>
        @endif

        {{-- Purchase Section --}}
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        هزینه آزمون
                    </h3>
                    @if($test->discounted_price)
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                                {{ number_format($test->discounted_price) }}
                            </span>
                            <span class="text-lg text-gray-500 line-through">
                                {{ number_format($test->price) }}
                            </span>
                            <span class="text-lg text-gray-500">تومان</span>
                            <span class="bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300 px-2 py-1 rounded text-sm font-bold">
                                {{ $test->discount_percentage }}% تخفیف
                            </span>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                                {{ number_format($test->price) }}
                            </span>
                            <span class="text-lg text-gray-500">تومان</span>
                        </div>
                    @endif

                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        موجودی کیف پول شما: {{ number_format(auth()->user()->wallet_balance) }} تومان
                    </div>
                </div>

                <div>
                    @if($hasPurchased)
                        <x-filament::button
                            disabled
                            color="success"
                            size="lg"
                        >
                            <x-heroicon-m-check-circle class="w-5 h-5 ml-2" />
                            خریداری شده
                        </x-filament::button>
                    @elseif($test->requires_family && !$hasFamily)
                        <div class="text-center">
                            <p class="text-sm text-warning-600 dark:text-warning-400 mb-2">
                                برای این آزمون نیاز به ثبت خانواده است
                            </p>
                            <x-filament::button
                                :href="route('filament.admin.resources.families.create')"
                                tag="a"
                                color="warning"
                                size="lg"
                            >
                                ثبت اطلاعات خانواده
                            </x-filament::button>
                        </div>
                    @elseif(auth()->user()->wallet_balance < ($test->discounted_price ?? $test->price))
                        <div class="text-center">
                            <p class="text-sm text-danger-600 dark:text-danger-400 mb-2">
                                موجودی کیف پول کافی نیست
                            </p>
                            <x-filament::button
                                :href="route('filament.admin.pages.wallet-charge')"
                                tag="a"
                                color="danger"
                                size="lg"
                            >
                                شارژ کیف پول
                            </x-filament::button>
                        </div>
                    @else
                        <x-filament::button
                            wire:click="openPurchaseModal"
                            color="primary"
                            size="lg"
                        >
                            <x-heroicon-m-shopping-cart class="w-5 h-5 ml-2" />
                            خرید و شروع آزمون
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Purchase Confirmation Modal --}}
    <x-filament::modal
        id="purchase-modal"
        :visible="$showPurchaseModal"
        width="md"
    >
        <x-slot name="header">
            <h2 class="text-lg font-semibold">تایید خرید آزمون</h2>
        </x-slot>

        <div class="space-y-4">
            <p>آیا از خرید این آزمون اطمینان دارید؟</p>

            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600 dark:text-gray-400">نام آزمون:</span>
                    <span class="font-semibold">{{ $test->title }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600 dark:text-gray-400">قیمت:</span>
                    <span class="font-semibold">{{ number_format($test->effective_price) }} تومان</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">موجودی بعد از خرید:</span>
                    <span class="font-semibold">
                        {{ number_format(auth()->user()->wallet_balance - $test->effective_price) }} تومان
                    </span>
                </div>
            </div>

            <div class="bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg p-4">
                <p class="text-sm text-warning-800 dark:text-warning-200">
                    <strong>توجه:</strong> پس از خرید، مبلغ از کیف پول شما کسر خواهد شد و امکان بازگشت وجود ندارد.
                </p>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex gap-3 justify-end">
                <x-filament::button
                    wire:click="closePurchaseModal"
                    color="gray"
                >
                    انصراف
                </x-filament::button>

                <x-filament::button
                    wire:click="purchase"
                    color="primary"
                >
                    <x-heroicon-m-check class="w-5 h-5 ml-2" />
                    تایید و خرید
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
