<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('اطلاعات تراکنش')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('شناسه تراکنش'),

                        Infolists\Components\TextEntry::make('user.full_name')
                            ->label('کاربر'),

                        Infolists\Components\TextEntry::make('type')
                            ->label('نوع تراکنش')
                            ->formatStateUsing(fn ($state) => $this->record->type_label)
                            ->badge()
                            ->color(fn () => $this->record->type_color),

                        Infolists\Components\TextEntry::make('amount')
                            ->label('مبلغ')
                            ->numeric()
                            ->suffix(' تومان'),

                        Infolists\Components\TextEntry::make('balance_before')
                            ->label('موجودی قبلی')
                            ->numeric()
                            ->suffix(' تومان'),

                        Infolists\Components\TextEntry::make('balance_after')
                            ->label('موجودی بعدی')
                            ->numeric()
                            ->suffix(' تومان'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('وضعیت')
                            ->formatStateUsing(fn ($state) => $this->record->status_label)
                            ->badge()
                            ->color(fn () => $this->record->status_color),

                        Infolists\Components\TextEntry::make('payment_gateway')
                            ->label('درگاه پرداخت'),

                        Infolists\Components\TextEntry::make('payment_ref_id')
                            ->label('کد پیگیری'),

                        Infolists\Components\TextEntry::make('description')
                            ->label('توضیحات')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاریخ ایجاد')
                            ->jalaliDateTime(),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('آخرین بروزرسانی')
                            ->jalaliDateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('داده‌های پرداخت')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_data')
                            ->label('اطلاعات اضافی')
                            ->formatStateUsing(function ($state) {
                                if (!$state || !is_array($state)) {
                                    return 'ندارد';
                                }

                                $html = '<dl class="space-y-2">';
                                foreach ($state as $key => $value) {
                                    $html .= sprintf(
                                        '<div><dt class="font-semibold inline">%s:</dt> <dd class="inline">%s</dd></div>',
                                        htmlspecialchars($key),
                                        htmlspecialchars($value)
                                    );
                                }
                                $html .= '</dl>';

                                return $html;
                            })
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn () => !empty($this->record->payment_data))
                    ->collapsed(),
            ]);
    }
}
