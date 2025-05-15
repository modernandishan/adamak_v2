<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Reactive;

class Profile extends BaseEditProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.auth.profile';
    protected static ?string $slug = 'profile';

    #[Reactive]
    public $showVerifyForm = false;

    public $otp = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('اطلاعات شخصی')
                    ->schema([
                        TextInput::make('first_name')
                            ->label('نام')
                            ->required(),
                        TextInput::make('last_name')
                            ->label('نام خانوادگی')
                            ->required(),
                        TextInput::make('mobile')
                            ->label('شماره موبایل')
                            ->tel()
                            ->required()
                            ->regex('/^09[0-9]{9}$/')
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('اطلاعات پروفایل')
                    ->relationship('profile')
                    ->schema([
                        Select::make('gender')
                            ->label('جنسیت')
                            ->options([
                                'male' => 'مرد',
                                'female' => 'زن',
                                'other' => 'سایر',
                            ]),
                        TextInput::make('relationship')
                            ->label('نسبت'),
                        TextInput::make('province')
                            ->label('استان'),
                        TextInput::make('city')
                            ->label('شهر'),
                        Textarea::make('address')
                            ->label('آدرس')
                            ->columnSpanFull(),
                        TextInput::make('postal_code')
                            ->label('کد پستی'),
                        DatePicker::make('birth_date')
                            ->label('تاریخ تولد'),
                        TextInput::make('national_code')
                            ->label('کد ملی'),
                        TextInput::make('education_level')
                            ->label('سطح تحصیلات'),
                    ]),

                $this->getPasswordFormComponent(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $actions = parent::getHeaderActions();

        if (auth()->user() && is_null(auth()->user()->mobile_verified_at)) {
            $actions[] = \Filament\Actions\Action::make('verify_mobile')
                ->label('تایید شماره موبایل')
                ->color('success')
                ->action(function () {
                    $this->showVerifyForm = true;
                    $this->sendOtp();
                });
        }

        return $actions;
    }

    protected function sendOtp()
    {
        $otpService = app(OtpService::class);
        $result = $otpService->sendOtp(auth()->user()->mobile);

        Notification::make()
            ->title('کد تایید ارسال شد')
            ->body('کد تایید به شماره موبایل شما ارسال شد')
            ->success()
            ->send();
    }

    public function verifyOtp()
    {
        $otpService = app(OtpService::class);

        if ($otpService->verifyOtp(auth()->user()->mobile, $this->otp)) {
            auth()->user()->update(['mobile_verified_at' => now()]);

            Notification::make()
                ->title('تایید موفق')
                ->body('شماره موبایل شما با موفقیت تایید شد')
                ->success()
                ->send();

            $this->showVerifyForm = false;
            $this->reset('otp');

            return redirect()->route('filament.admin.pages.dashboard');
        } else {
            Notification::make()
                ->title('خطا')
                ->body('کد وارد شده نامعتبر است')
                ->danger()
                ->send();
        }
    }

    public function resendOtp()
    {
        $this->sendOtp();
    }
}
