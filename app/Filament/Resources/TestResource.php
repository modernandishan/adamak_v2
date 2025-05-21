<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestResource\Pages;
use App\Models\Test;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Str;

class TestResource extends Resource
{
    protected static ?string $model = Test::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'آزمون‌ها';

    protected static ?string $modelLabel = 'آزمون';

    protected static ?string $pluralModelLabel = 'آزمون‌ها';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('اطلاعات اصلی')
                            ->schema([
                                Forms\Components\Select::make('test_category_id')
                                    ->label('دسته‌بندی')
                                    ->relationship('category', 'title')
                                    ->required()
                                    ->preload()
                                    ->searchable(),

                                Forms\Components\TextInput::make('title')
                                    ->label('عنوان')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $state, Forms\Set $set) => $set('slug', Str::slug($state))),

                                Forms\Components\TextInput::make('slug')
                                    ->label('اسلاگ')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\Textarea::make('short_description')
                                    ->label('توضیحات کوتاه')
                                    ->maxLength(500)
                                    ->columnSpanFull(),

                                TiptapEditor::make('description')
                                    ->label('توضیحات کامل')
                                    ->profile('default')
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('featured_image')
                                    ->label('تصویر شاخص')
                                    ->image()
                                    ->directory('tests/featured-images')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('تنظیمات قیمت')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('قیمت (تومان)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),

                                Forms\Components\TextInput::make('discounted_price')
                                    ->label('قیمت با تخفیف (تومان)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->lte('price'),

                                Forms\Components\Placeholder::make('discount_percentage')
                                    ->label('درصد تخفیف')
                                    ->content(function ($get) {
                                        $price = (float) $get('price');
                                        $discountedPrice = (float) $get('discounted_price');

                                        if (!$discountedPrice || $price == 0) {
                                            return '0%';
                                        }

                                        return round((1 - ($discountedPrice / $price)) * 100) . '%';
                                    }),
                            ]),

                        Forms\Components\Section::make('سوالات')
                            ->schema([
                                Forms\Components\Repeater::make('questions')
                                    ->label('سوالات آزمون')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label('نوع سوال')
                                            ->options([
                                                'text' => 'متنی',
                                                'choice' => 'تک انتخابی',
                                                'multiple_choice' => 'چند انتخابی',
                                                'upload' => 'آپلود فایل',
                                                'drawing' => 'نقاشی',
                                                'rating' => 'امتیازدهی',
                                            ])
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn (Forms\Set $set) => $set('options', [])),

                                        Forms\Components\Textarea::make('question_text')
                                            ->label('متن سوال')
                                            ->required()
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('instruction')
                                            ->label('راهنمای سوال')
                                            ->columnSpanFull(),

                                        Forms\Components\KeyValue::make('options')
                                            ->label('گزینه‌ها')
                                            ->addButtonLabel('افزودن گزینه')
                                            ->keyLabel('شناسه')
                                            ->valueLabel('متن گزینه')
                                            ->default([]) // اضافه کردن مقدار پیش‌فرض
                                            ->columnSpanFull()
                                            ->visible(fn ($get) => in_array($get('type'), ['choice', 'multiple_choice'])),

                                        Forms\Components\TextInput::make('stage')
                                            ->label('مرحله')
                                            ->numeric()
                                            ->default(1)
                                            ->visible(fn ($get, $record) => $record?->test?->is_multi_stage || $get('../is_multi_stage')),

                                        Forms\Components\TextInput::make('order')
                                            ->label('ترتیب')
                                            ->numeric()
                                            ->default(0),

                                        Forms\Components\Toggle::make('is_required')
                                            ->label('اجباری')
                                            ->default(true),
                                    ])
                                    ->orderable('order')
                                    ->defaultItems(0)
                                    ->reorderable()
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('وضعیت')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('فعال')
                                    ->default(true),

                                Forms\Components\Toggle::make('requires_family')
                                    ->label('نیاز به ثبت خانواده')
                                    ->default(false)
                                    ->helperText('آیا برای شرکت در این آزمون، کاربر باید خانواده‌ای ثبت کرده باشد؟'),

                                Forms\Components\Toggle::make('is_multi_stage')
                                    ->label('چند مرحله‌ای')
                                    ->default(false)
                                    ->helperText('آیا این آزمون شامل چندین مرحله است؟')
                                    ->live(),

                                Forms\Components\TextInput::make('estimated_completion_time')
                                    ->label('زمان تقریبی تکمیل (دقیقه)')
                                    ->numeric()
                                    ->minValue(1),

                                Forms\Components\TextInput::make('order')
                                    ->label('ترتیب')
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Forms\Components\Section::make('تنظیمات پیشرفته')
                            ->schema([
                                Forms\Components\KeyValue::make('settings')
                                    ->label('تنظیمات اضافی')
                                    ->keyLabel('کلید')
                                    ->valueLabel('مقدار')
                                    ->addButtonLabel('افزودن تنظیم'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('تصویر')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.title')
                    ->label('دسته‌بندی')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('قیمت (تومان)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('تعداد سوالات')
                    ->counts('questions'),

                Tables\Columns\IconColumn::make('requires_family')
                    ->label('نیاز به خانواده')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_multi_stage')
                    ->label('چند مرحله‌ای')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDate()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('test_category_id')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'title')
                    ->preload()
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعال'),

                Tables\Filters\TernaryFilter::make('requires_family')
                    ->label('نیاز به خانواده'),

                Tables\Filters\TernaryFilter::make('is_multi_stage')
                    ->label('چند مرحله‌ای'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTests::route('/'),
            'create' => Pages\CreateTest::route('/create'),
            'edit' => Pages\EditTest::route('/{record}/edit'),
        ];
    }
}
