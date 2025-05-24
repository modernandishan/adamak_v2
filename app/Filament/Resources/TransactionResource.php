<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'کیف پول';

    protected static ?string $modelLabel = 'تراکنش';

    protected static ?string $pluralModelLabel = 'تراکنش‌ها';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات تراکنش')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('کاربر')
                            ->relationship('user', 'first_name')
                            ->disabled()
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('نوع تراکنش')
                            ->options([
                                Transaction::TYPE_DEPOSIT => 'شارژ کیف پول',
                                Transaction::TYPE_PURCHASE => 'خرید',
                                Transaction::TYPE_REFERRAL_BONUS => 'پاداش معرفی',
                                Transaction::TYPE_ADMIN_ADJUSTMENT => 'تنظیم توسط مدیر',
                                Transaction::TYPE_REFUND => 'بازگشت وجه',
                            ])
                            ->disabled()
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('مبلغ')
                            ->numeric()
                            ->disabled()
                            ->suffix('تومان'),

                        Forms\Components\TextInput::make('balance_before')
                            ->label('موجودی قبلی')
                            ->numeric()
                            ->disabled()
                            ->suffix('تومان'),

                        Forms\Components\TextInput::make('balance_after')
                            ->label('موجودی بعدی')
                            ->numeric()
                            ->disabled()
                            ->suffix('تومان'),

                        Forms\Components\Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                Transaction::STATUS_PENDING => 'در انتظار',
                                Transaction::STATUS_COMPLETED => 'موفق',
                                Transaction::STATUS_FAILED => 'ناموفق',
                            ])
                            ->disabled()
                            ->required(),

                        Forms\Components\TextInput::make('payment_gateway')
                            ->label('درگاه پرداخت')
                            ->disabled(),

                        Forms\Components\TextInput::make('payment_ref_id')
                            ->label('کد پیگیری')
                            ->disabled(),

                        Forms\Components\Textarea::make('description')
                            ->label('توضیحات')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('کاربر')
                    ->searchable(['first_name', 'last_name'])
                    ->visible(fn () => auth()->user()->hasRole(['admin', 'super_admin'])),

                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->formatStateUsing(fn ($state) => $state ? Transaction::find(1)->getTypeLabelAttribute() : '')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        Transaction::TYPE_DEPOSIT => 'success',
                        Transaction::TYPE_PURCHASE => 'danger',
                        Transaction::TYPE_REFERRAL_BONUS => 'info',
                        Transaction::TYPE_ADMIN_ADJUSTMENT => 'warning',
                        Transaction::TYPE_REFUND => 'secondary',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('مبلغ')
                    ->numeric()
                    ->suffix(' تومان')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => match($state) {
                        Transaction::STATUS_COMPLETED => 'موفق',
                        Transaction::STATUS_PENDING => 'در انتظار',
                        Transaction::STATUS_FAILED => 'ناموفق',
                        default => 'نامشخص',
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        Transaction::STATUS_COMPLETED => 'success',
                        Transaction::STATUS_PENDING => 'warning',
                        Transaction::STATUS_FAILED => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('payment_ref_id')
                    ->label('کد پیگیری')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('موجودی جدید')
                    ->numeric()
                    ->suffix(' تومان')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->jalaliDateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع تراکنش')
                    ->options([
                        Transaction::TYPE_DEPOSIT => 'شارژ کیف پول',
                        Transaction::TYPE_PURCHASE => 'خرید',
                        Transaction::TYPE_REFERRAL_BONUS => 'پاداش معرفی',
                        Transaction::TYPE_ADMIN_ADJUSTMENT => 'تنظیم توسط مدیر',
                        Transaction::TYPE_REFUND => 'بازگشت وجه',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        Transaction::STATUS_PENDING => 'در انتظار',
                        Transaction::STATUS_COMPLETED => 'موفق',
                        Transaction::STATUS_FAILED => 'ناموفق',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // اگر کاربر ادمین نیست، فقط تراکنش‌های خودش را ببیند
        if (!auth()->user()->hasRole(['admin', 'super_admin'])) {
            $query->where('user_id', auth()->id());
        }

        return $query;
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
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // تراکنش‌ها فقط توسط سیستم ایجاد می‌شوند
    }

    public static function canDelete($record): bool
    {
        return false; // تراکنش‌ها قابل حذف نیستند
    }
}
