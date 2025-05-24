<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WalletBalanceWidget extends Widget
{
    protected static string $view = 'filament.widgets.wallet-balance-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -2;

    public function getWalletBalance(): string
    {
        return number_format(Auth::user()->wallet_balance);
    }

    public function getLastTransactions(): array
    {
        return Auth::user()
            ->transactions()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'type' => $transaction->type_label,
                    'amount' => number_format($transaction->amount),
                    'status' => $transaction->status_label,
                    'status_color' => $transaction->status_color,
                    'date' => $transaction->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }
}
