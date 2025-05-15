<?php

namespace App\Filament\Pages\Auth;

use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use App\Models\User;
class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getFirstNameFormComponent(),
                $this->getLastNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getFirstNameFormComponent(): Component
    {
        return TextInput::make('first_name')
            ->label('نام')
            ->required()
            ->maxLength(255);
    }

    protected function getLastNameFormComponent(): Component
    {
        return TextInput::make('last_name')
            ->label('نام خانوادگی')
            ->required()
            ->maxLength(255);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('mobile')
            ->label('شماره موبایل')
            ->minLength(11)
            ->maxLength(11)
            ->tel()
            ->regex('/^09[0-9]{9}$/')
            ->required()
            ->unique(User::class, 'mobile')
            ->autocomplete('off');
    }

    protected function getUserData(): array
    {
        $data = $this->form->getState();

        return [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'mobile' => $data['mobile'],
            'password' => $data['password'],
        ];
    }

    public static function getModel(): string
    {
        return User::class;
    }

    protected function handleRegistration(array $data): Model
    {
        $user = static::getModel()::create($data);

        $user->assignRole('user');

        return $user;
    }
}
