<?php

use App\Filament\Pages\TestDetails;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/charge', [WalletController::class, 'showChargeForm'])->name('wallet.charge.form');
    Route::post('/wallet/charge', [WalletController::class, 'processCharge'])->name('wallet.charge.process');
    Route::get('/wallet/callback', [WalletController::class, 'handleCallback'])->name('wallet.callback');
});
Route::get('/adamak/test-details/{test}', TestDetails::class)->name('adamak.test-details');
