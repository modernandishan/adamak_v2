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
use Carbon\Carbon;

class Profile extends BaseEditProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.auth.profile';
    protected static ?string $slug = 'profile';

    public $showVerifyForm = false;
    public $otp = '';
    public $resendAttempts = 0;
    public $lastSentAt = null;
    public $maxAttempts = 5;
    public $resendCooldown = 60; // 60 ثانیه
    public $remainingTime = 0;

    // برای ردیابی تغییر شماره موبایل
    public $originalMobile;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('اطلاعات شخصی')
                    ->schema([
                        TextInput::make('first_name')
                            ->label('نام')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'نام الزامی است',
                                'max' => 'نام نباید بیشتر از 255 کاراکتر باشد',
                            ]),

                        TextInput::make('last_name')
                            ->label('نام خانوادگی')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'نام خانوادگی الزامی است',
                                'max' => 'نام خانوادگی نباید بیشتر از 255 کاراکتر باشد',
                            ]),

                        TextInput::make('mobile')
                            ->label('شماره موبایل')
                            ->tel()
                            ->required()
                            ->regex('/^09[0-9]{9}$/')
                            ->unique(ignoreRecord: true)
                            ->reactive()
                            ->validationMessages([
                                'required' => 'شماره موبایل الزامی است',
                                'regex' => 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد',
                                'unique' => 'این شماره موبایل قبلاً ثبت شده است',
                            ])
                            ->afterStateUpdated(function ($state) {
                                // اگر شماره موبایل تغییر کرد، verification را null کن
                                if ($this->originalMobile && $this->originalMobile !== $state) {
                                    $this->record->update(['mobile_verified_at' => null]);

                                    Notification::make()
                                        ->title('تغییر شماره موبایل')
                                        ->body('با تغییر شماره موبایل، نیاز است دوباره آن را تایید کنید.')
                                        ->warning()
                                        ->send();
                                }
                            }),
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
                            ])
                            ->native(false),

                        Select::make('relationship')
                            ->label('نسبت')
                            ->options([
                                'پدر' => 'پدر',
                                'مادر' => 'مادر',
                                'برادر' => 'برادر',
                                'خواهر' => 'خواهر',
                                'پدر بزرگ' => 'پدر بزرگ',
                                'مادر بزرگ' => 'مادر بزرگ',
                                'خاله' => 'خاله',
                                'عمه' => 'عمه',
                                'دایی' => 'دایی',
                                'عمو' => 'عمو',
                                'دیگر' => 'دیگر',
                            ])
                            ->native(false),

                        TextInput::make('province')
                            ->label('استان')
                            ->maxLength(255),

                        TextInput::make('city')
                            ->label('شهر')
                            ->maxLength(255),

                        Textarea::make('address')
                            ->nullable()
                            ->label('آدرس')
                            ->minLength(31)
                            ->maxLength(255)
                            ->helperText('حداقل 31 و حداکثر 255 کاراکتر')
                            ->columnSpanFull()
                            ->rows(3)
                            ->validationMessages([
                                'min' => 'آدرس باید حداقل 31 کاراکتر باشد',
                                'max' => 'آدرس نباید بیشتر از 255 کاراکتر باشد',
                            ]),

                        TextInput::make('postal_code')
                            ->label('کد پستی')
                            ->numeric()
                            ->length(10)
                            ->regex('/^[0-9]{10}$/')
                            ->helperText('باید دقیقاً 10 رقم باشد')
                            ->validationMessages([
                                'regex' => 'کد پستی باید دقیقاً 10 رقم باشد',
                                'size' => 'کد پستی باید دقیقاً 10 رقم باشد',
                            ]),

                        DatePicker::make('birth_date')
                            ->jalali()
                            ->label('تاریخ تولد')
                            ->helperText('تاریخ تولد خود را انتخاب کنید'),

                        TextInput::make('national_code')
                            ->label('کد ملی')
                            ->numeric()
                            ->length(10)
                            ->regex('/^[0-9]{10}$/')
                            ->unique(ignoreRecord: true)
                            ->helperText('باید دقیقاً 10 رقم باشد')
                            ->validationMessages([
                                'regex' => 'کد ملی باید دقیقاً 10 رقم باشد',
                                'size' => 'کد ملی باید دقیقاً 10 رقم باشد',
                                'unique' => 'این کد ملی قبلاً ثبت شده است',
                            ]),

                        TextInput::make('education_level')
                            ->label('سطح تحصیلات')
                            ->maxLength(255),
                    ]),

                $this->getPasswordFormComponent(),
            ])
            ->statePath('data');
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
        // بررسی تعداد تلاش‌ها
        if ($this->resendAttempts >= $this->maxAttempts) {
            Notification::make()
                ->title('محدودیت تلاش')
                ->body('شما بیشتر از حد مجاز تلاش کرده‌اید. لطفاً بعداً دوباره تلاش کنید.')
                ->danger()
                ->send();
            return;
        }

        // بررسی زمان آخرین ارسال
        if ($this->lastSentAt && $this->getRemainingCooldownTime() > 0) {
            $remainingSeconds = $this->getRemainingCooldownTime();
            Notification::make()
                ->title('لطفاً صبر کنید')
                ->body("می‌توانید بعد از {$remainingSeconds} ثانیه دوباره درخواست دهید.")
                ->warning()
                ->send();
            return;
        }

        $otpService = app(OtpService::class);
        $result = $otpService->sendOtp(auth()->user()->mobile);

        // به‌روزرسانی اطلاعات
        $this->lastSentAt = now();
        $this->resendAttempts++;

        if ($result['is_new']) {
            Notification::make()
                ->title('کد تایید ارسال شد')
                ->body('کد تایید به شماره موبایل شما ارسال شد')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('کد تایید موجود')
                ->body('کد تایید قبلاً برای شما ارسال شده است.')
                ->info()
                ->send();
        }

        // راه‌اندازی timer برای به‌روزرسانی UI
        $this->dispatch('startCooldownTimer');
    }

    public function verifyOtp()
    {
        $this->validate([
            'otp' => 'required|string|min:4|max:6'
        ]);

        $otpService = app(OtpService::class);

        if ($otpService->verifyOtp(auth()->user()->mobile, $this->otp)) {
            auth()->user()->update(['mobile_verified_at' => now()]);

            Notification::make()
                ->title('تایید موفق')
                ->body('شماره موبایل شما با موفقیت تایید شد')
                ->success()
                ->send();

            $this->resetOtpState();
            return redirect()->route('filament.admin.pages.dashboard');
        } else {
            $this->addError('otp', 'کد وارد شده نامعتبر است');

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
        $this->reset('otp');
    }

    public function getRemainingCooldownTime(): int
    {
        if (!$this->lastSentAt) {
            return 0;
        }

        $elapsed = Carbon::parse($this->lastSentAt)->diffInSeconds(now());
        $remaining = max(0, $this->resendCooldown - $elapsed);

        return $remaining;
    }

    public function getCanResend(): bool
    {
        return $this->resendAttempts < $this->maxAttempts && $this->getRemainingCooldownTime() <= 0;
    }

    public function getRemainingAttempts(): int
    {
        return max(0, $this->maxAttempts - $this->resendAttempts);
    }

    public function updateCooldownTime()
    {
        $this->remainingTime = $this->getRemainingCooldownTime();
    }

    protected function resetOtpState()
    {
        $this->showVerifyForm = false;
        $this->otp = '';
        $this->resendAttempts = 0;
        $this->lastSentAt = null;
        $this->remainingTime = 0;
    }

    public function mount(): void
    {
        parent::mount();
        $this->resetOtpState();

        // ذخیره شماره موبایل اصلی برای ردیابی تغییرات
        $this->originalMobile = auth()->user()->mobile;
    }

    // Helper method برای فرمت موجودی کیف پول
    public function getFormattedWalletBalance(): string
    {
        $balance = auth()->user()->wallet_balance ?? 0;
        return number_format($balance) . ' تومان';
    }

    // Override کردن متد save برای نمایش پیام موفقیت
    public function save(): void
    {
        try {
            parent::save();

            /*Notification::make()
                ->title('اطلاعات ذخیره شد')
                ->body('اطلاعات پروفایل شما با موفقیت به‌روزرسانی شد.')
                ->success()
                ->send();*/

        } catch (\Exception $e) {
            Notification::make()
                ->title('خطا در ذخیره‌سازی')
                ->body('مشکلی در ذخیره اطلاعات پیش آمد. لطفاً دوباره تلاش کنید.')
                ->danger()
                ->send();
        }
    }
}
