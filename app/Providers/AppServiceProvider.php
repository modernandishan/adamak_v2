<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Infolist;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        User::observe(UserObserver::class);

        Table::$defaultDateDisplayFormat = 'Y/m/d';
        Table::$defaultDateTimeDisplayFormat = 'Y/m/d H:i:s';
        Infolist::$defaultDateDisplayFormat = 'Y/m/d';
        Infolist::$defaultDateTimeDisplayFormat = 'Y/m/d H:i:s';
        DateTimePicker::$defaultDateDisplayFormat = 'Y/m/d';
        DateTimePicker::$defaultDateTimeDisplayFormat = 'Y/m/d H:i';
        DateTimePicker::$defaultDateTimeWithSecondsDisplayFormat = 'Y/m/d H:i:s';
    }
}
