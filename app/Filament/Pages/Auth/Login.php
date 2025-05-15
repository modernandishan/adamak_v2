<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('mobile')
            ->label('شماره موبایل')
            ->minLength(11)
            ->maxLength(11)
            ->tel()
            ->regex('/^09[0-9]{9}$/')
            ->required()
            ->autocomplete('off');
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'mobile' => $data['mobile'],
            'password' => $data['password'],
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        $this->validate();

        $mobile = $this->data['mobile'];
        $password = $this->data['password'];

        $user = User::where('mobile', $mobile)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            Notification::make()
                ->title('ورود ناموفق')
                ->body('شماره موبایل یا رمز عبور اشتباه است.')
                ->danger()
                ->send();

            $this->addError('mobile', 'اطلاعات ورود اشتباه است.');
            return null;
        }

        Auth::login($user);

        Notification::make()
            ->title('خوش آمدید!')
            ->body('شما با موفقیت وارد شدید.')
            ->success()
            ->send();

        return app(LoginResponse::class);
    }
}
