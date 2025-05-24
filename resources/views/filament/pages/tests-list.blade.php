<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Search and Filter Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Search Input --}}
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        جستجو در آزمون‌ها
                    </label>
                    <div class="relative">
                        <input
                            wire:model.live.debounce.300ms="searchQuery"
                            type="text"
                            id="search"
                            class="w-full px-4 py-2 pl-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                            placeholder="نام آزمون را جستجو کنید..."
                        >
                        <x-heroicon-m-magnifying-glass class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                    </div>
                </div>

                {{-- Category Filter --}}
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        دسته‌بندی
                    </label>
                    <select
                        wire:model.live="selectedCategory"
                        id="category"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">همه دسته‌ها</option>
                        @foreach($this->getCategories() as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->title }} ({{ $category->tests_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Tests Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($this->getTests() as $test)
                <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden">
                    {{-- Featured Image --}}
                    @if($test->featured_image)
                        <div class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
                            <img
                                src="{{ Storage::url($test->featured_image) }}"
                                alt="{{ $test->title }}"
                                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                            >
                        </div>
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                            <x-heroicon-o-academic-cap class="w-20 h-20 text-white opacity-50" />
                        </div>
                    @endif

                    {{-- Badge --}}
                    @if($test->discounted_price)
                        <div class="absolute top-4 right-4 bg-danger-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                            {{ $test->discount_percentage }}% تخفیف
                        </div>
                    @endif

                    {{-- Content --}}
                    <div class="p-6">
                        {{-- Category --}}
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $test->category->title }}
                            </span>
                            @if($test->is_multi_stage)
                                <span class="text-xs bg-info-100 text-info-700 dark:bg-info-900 dark:text-info-300 px-2 py-0.5 rounded">
                                    چند مرحله‌ای
                                </span>
                            @endif
                        </div>

                        {{-- Title --}}
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2">
                            {{ $test->title }}
                        </h3>

                        {{-- Description --}}
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                            {{ $test->short_description }}
                        </p>

                        {{-- Meta Info --}}
                        <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-4">
                            <div class="flex items-center gap-1">
                                <x-heroicon-m-question-mark-circle class="w-4 h-4" />
                                <span>{{ $test->questions_count }} سوال</span>
                            </div>
                            @if($test->estimated_completion_time)
                                <div class="flex items-center gap-1">
                                    <x-heroicon-m-clock class="w-4 h-4" />
                                    <span>{{ $test->estimated_completion_time }} دقیقه</span>
                                </div>
                            @endif
                        </div>

                        {{-- Price --}}
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                @if($test->discounted_price)
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                            {{ number_format($test->discounted_price) }}
                                        </span>
                                        <span class="text-sm text-gray-500 line-through">
                                            {{ number_format($test->price) }}
                                        </span>
                                        <span class="text-sm text-gray-500">تومان</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1">
                                        <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                            {{ number_format($test->price) }}
                                        </span>
                                        <span class="text-sm text-gray-500">تومان</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <x-filament::button
                            :href="route('filament.admin.pages.test-details', ['test' => $test->id])"
                            tag="a"
                            color="primary"
                            class="w-full"
                        >
                            مشاهده جزئیات
                        </x-filament::button>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-12">
                        <x-heroicon-o-academic-cap class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            آزمونی یافت نشد
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            در حال حاضر آزمونی برای نمایش وجود ندارد.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
