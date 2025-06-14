<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'مدیریت کاربران';

    protected static ?string $modelLabel = 'کاربر';

    protected static ?string $pluralModelLabel = 'کاربران';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات اصلی')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('نام')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->label('نام خانوادگی')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('mobile')
                            ->label('شماره موبایل')
                            ->required()
                            ->tel()
                            ->unique(ignoreRecord: true)
                            ->regex('/^09[0-9]{9}$/')
                            ->minLength(11)
                            ->maxLength(11)
                            ->validationMessages([
                                'regex' => 'شماره موبایل باید با 09 شروع شود و دقیقاً 11 رقم باشد',
                                'min_length' => 'شماره موبایل باید دقیقاً 11 رقم باشد',
                                'max_length' => 'شماره موبایل باید دقیقاً 11 رقم باشد',
                            ]),

                        Forms\Components\DateTimePicker::make('mobile_verified_at')
                            ->jalali()
                            ->label('تاریخ تایید موبایل')
                            ->nullable(),

                        Forms\Components\TextInput::make('password')
                            ->label('رمز عبور')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),

                        Forms\Components\TextInput::make('wallet_balance')
                            ->label('موجودی کیف پول')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('referral_code')
                            ->label('کد معرف')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('referrer_id')
                            ->label('معرف')
                            ->relationship('referrer', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),

                Forms\Components\Section::make('اطلاعات پروفایل')
                    ->relationship('profile')
                    ->schema([
                        Forms\Components\Select::make('gender')
                            ->label('جنسیت')
                            ->options([
                                'male' => 'مرد',
                                'female' => 'زن',
                                'other' => 'سایر',
                            ])
                            ->native(false),

                        Forms\Components\TextInput::make('relationship')
                            ->label('نسبت')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('province')
                            ->label('استان')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                            ->label('شهر')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->label('آدرس')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('postal_code')
                            ->label('کد پستی')
                            ->regex('/^[0-9]{10}$/')
                            ->length(10)
                            ->numeric()
                            ->validationMessages([
                                'regex' => 'کد پستی باید دقیقاً 10 رقم باشد',
                                'length' => 'کد پستی باید دقیقاً 10 رقم باشد',
                            ]),

                        Forms\Components\DatePicker::make('birth_date')
                            ->jalali()
                            ->label('تاریخ تولد'),

                        Forms\Components\TextInput::make('national_code')
                            ->label('کد ملی')
                            ->regex('/^[0-9]{10}$/')
                            ->length(10)
                            ->numeric()
                            ->unique('profiles', 'national_code', ignoreRecord: true)
                            ->validationMessages([
                                'regex' => 'کد ملی باید دقیقاً 10 رقم باشد',
                                'length' => 'کد ملی باید دقیقاً 10 رقم باشد',
                                'unique' => 'این کد ملی قبلاً ثبت شده است',
                            ]),
                    ]),

                Forms\Components\Section::make('نقش‌ها و مجوزها')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('نقش‌ها')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('نام کامل')
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('شماره موبایل')
                    ->searchable(),

                Tables\Columns\IconColumn::make('mobile_verified_at')
                    ->label('تایید موبایل')
                    ->boolean(),

                Tables\Columns\TextColumn::make('wallet_balance')
                    ->label('موجودی کیف پول')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('نقش‌ها')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->jalaliDate()
                    ->label('تاریخ ثبت نام')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('نقش')
                    ->relationship('roles', 'name')
                    ->multiple(),

                Tables\Filters\Filter::make('mobile_verified')
                    ->label('تایید موبایل')
                    ->query(fn ($query) => $query->whereNotNull('mobile_verified_at')),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
