<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamilyResource\Pages;
use App\Filament\Resources\FamilyResource\RelationManagers;
use App\Models\Family;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FamilyResource extends Resource
{
    protected static ?string $model = Family::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'مدیریت کاربر';

    protected static ?string $modelLabel = 'خانواده';

    protected static ?string $pluralModelLabel = 'خانواده‌ها';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات خانواده')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان خانواده')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('توضیحات')
                            ->nullable()
                            ->maxLength(1000),
                    ]),

                Forms\Components\Section::make('اعضای خانواده')
                    ->schema([
                        Forms\Components\Repeater::make('members')
                            ->label('اعضای خانواده')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('نام')
                                    ->nullable(),

                                Forms\Components\Select::make('role')
                                    ->label('نقش')
                                    ->options([
                                        'پدر' => 'پدر',
                                        'مادر' => 'مادر',
                                        'پسر' => 'پسر',
                                        'دختر' => 'دختر',
                                    ])
                                    ->required(),

                                Forms\Components\DatePicker::make('birth_date')
                                    ->label('تاریخ تولد')
                                    ->jalali()
                                    ->nullable(),
                            ])
                            ->defaultItems(1)
                            ->minItems(1)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان خانواده')
                    ->searchable(),

                // اضافه کردن ستون نمایش صاحب خانواده
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('صاحب خانواده')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    // نمایش ستون فقط برای ادمین و سوپر ادمین
                    ->visible(fn () => auth()->user()->hasRole(['admin', 'super_admin'])),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('تعداد اعضا')
                    ->counts('members'),

                Tables\Columns\TextColumn::make('created_at')
                    ->jalaliDate()
                    ->label('تاریخ ایجاد')
                    ->sortable(),
            ])
            ->filters([
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilies::route('/'),
            'create' => Pages\CreateFamily::route('/create'),
            'edit' => Pages\EditFamily::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where(function ($query) {
                // اگر کاربر ادمین یا سوپر ادمین است، تمام خانواده‌ها را نمایش بده
                if (auth()->user()->hasRole(['admin', 'super_admin'])) {
                    return $query;
                }

                // در غیر این صورت، فقط خانواده‌های خود کاربر را نمایش بده
                return $query->where('user_id', auth()->id());
            });
    }

    // افزودن user_id به رکورد هنگام ذخیره
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
