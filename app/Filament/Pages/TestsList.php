<?php

namespace App\Filament\Pages;

use App\Models\Test;
use App\Models\TestCategory;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class TestsList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'آزمون‌ها';
    protected static ?string $title = 'لیست آزمون‌ها';
    protected static ?string $navigationLabel = 'آزمون‌های من';
    //protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.tests-list';

    public $selectedCategory = null;
    public $searchQuery = '';

    public function mount(): void
    {
        $this->selectedCategory = request()->get('category');
    }

    public function getTests()
    {
        return Test::query()
            ->with(['category', 'questions'])
            ->where('is_active', true)
            ->when($this->selectedCategory, function (Builder $query) {
                $query->where('test_category_id', $this->selectedCategory);
            })
            ->when($this->searchQuery, function (Builder $query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->searchQuery}%")
                        ->orWhere('short_description', 'like', "%{$this->searchQuery}%");
                });
            })
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCategories()
    {
        return TestCategory::query()
            ->where('is_active', true)
            ->withCount(['tests' => function ($query) {
                $query->where('is_active', true);
            }])
            ->having('tests_count', '>', 0)
            ->orderBy('order')
            ->get();
    }

    public function updatedSelectedCategory()
    {
        $this->dispatch('category-changed');
    }

    public function updatedSearchQuery()
    {
        $this->dispatch('search-changed');
    }
}
