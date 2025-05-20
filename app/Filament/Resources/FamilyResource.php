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

                Tables\Columns\TextColumn::make('members_count')
                    ->label('تعداد اعضا')
                    ->counts('members'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListFamilies::route('/'),
            'create' => Pages\CreateFamily::route('/create'),
            'edit' => Pages\EditFamily::route('/{record}/edit'),
        ];
    }

    // محدود کردن نمایش خانواده‌ها به خانواده‌های کاربر جاری
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    // افزودن user_id به رکورد هنگام ذخیره
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
