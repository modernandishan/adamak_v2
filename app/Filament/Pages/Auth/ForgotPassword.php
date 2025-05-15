<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\OtpService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

class ForgotPassword extends RequestPasswordReset
{
    public string $step = 'mobile';
    public ?string $email = null;
    public string $mobile = '';
    public string $otp = '';
    public string $password = '';
    public string $passwordConfirmation = '';

    protected $listeners = ['refreshForm' => '$refresh'];

    public function request(): void
    {
        // اختصاص مقدار mobile به email برای سازگاری با کلاس پایه
        if (isset($this->data['mobile'])) {
            $this->mobile = $this->data['mobile'];
            $this->email = $this->data['mobile'];
        }

        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return;
        }

        // بر اساس مرحله فعلی، متد مناسب را فراخوانی می‌کنیم
        if ($this->step === 'mobile') {
            $this->handleMobileStep();
            return;
        } elseif ($this->step === 'otp') {
            $this->handleOtpStep();
            return;
        } elseif ($this->step === 'reset') {
            $this->handleResetStep();
            return;
        }
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
            ->autocomplete('off');
    }

    public function form(Form $form): Form
    {
        $schema = [];

        if ($this->step === 'mobile') {
            $schema[] = $this->getEmailFormComponent();
        } elseif ($this->step === 'otp') {
            $schema[] = TextInput::make('otp')
                ->label('کد تایید')
                ->required()
                ->numeric()
                ->placeholder('کد ارسال شده را وارد کنید');
        } elseif ($this->step === 'reset') {
            $schema[] = TextInput::make('password')
                ->label('رمز عبور جدید')
                ->password()
                ->required()
                ->minLength(8)
                ->same('passwordConfirmation')
                ->autocomplete('new-password');

            $schema[] = TextInput::make('passwordConfirmation')
                ->label('تکرار رمز عبور جدید')
                ->password()
                ->required()
                ->minLength(8)
                ->dehydrated(false)
                ->autocomplete('new-password');
        }

        return $form->schema($schema);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('request')
                ->label(match ($this->step) {
                    'mobile' => 'دریافت کد تایید',
                    'otp' => 'تایید کد',
                    'reset' => 'تغییر رمز عبور',
                })
                ->submit('request'),
        ];
    }

    // این متد به فرم اطلاع می‌دهد که باید به روز شود
    public function updatedStep($value)
    {
        // حفظ مقدار موبایل
        if (isset($this->data['mobile'])) {
            $this->mobile = $this->data['mobile'];
        }

        // بازنشانی داده‌های فرم
        $this->reset('data');
        $this->form->fill([]);

        // به‌روزرسانی اجباری DOM
        $this->dispatch('refreshForm');
    }

    protected function handleMobileStep()
    {
        $user = User::where('mobile', $this->data['mobile'])->first();

        if (! $user) {
            Notification::make()
                ->title('خطا')
                ->body('این شماره موبایل در سیستم ثبت نشده است.')
                ->danger()
                ->send();
            return;
        }

        // ارسال کد OTP
        $otpService = app(OtpService::class);
        $code = $otpService->sendOtp($this->data['mobile']);

        // برای اهداف توسعه، کد را نمایش می‌دهیم (در محیط تولید این خط را حذف کنید)
        Notification::make()
            ->title('کد تایید ارسال شد')
            ->body("کد تایید شما: {$code}")
            ->success()
            ->send();

        // ذخیره موبایل در متغیر جلسه
        $this->mobile = $this->data['mobile'];
        session(['reset_password_mobile' => $this->mobile]);

        // خالی کردن فرم قبلی
        $this->reset('data');
        $this->form->fill([]);

        // رفتن به مرحله بعد - این خط فراخوانی متد updatedStep را تریگر می‌کند
        $this->step = 'otp';

        // رفرش اجباری صفحه
        $this->dispatch('refreshForm');
    }

    protected function handleOtpStep()
    {
        // بررسی صحت کد OTP
        $otpService = app(OtpService::class);

        // استفاده از مقدار ذخیره شده در جلسه یا متغیر کلاس
        $mobile = session('reset_password_mobile', $this->mobile);
        $otp = $this->data['otp'] ?? '';

        $isValid = $otpService->verifyOtp($mobile, $otp);

        if (! $isValid) {
            Notification::make()
                ->title('خطا')
                ->body('کد وارد شده نامعتبر است یا منقضی شده است.')
                ->danger()
                ->send();
            return;
        }

        // نمایش پیام موفقیت
        Notification::make()
            ->title('کد تایید شد')
            ->body('لطفاً رمز عبور جدید خود را وارد کنید.')
            ->success()
            ->send();

        // خالی کردن فرم قبلی
        $this->reset('data');
        $this->form->fill([]);

        // رفتن به مرحله بعد
        $this->step = 'reset';

        // رفرش اجباری صفحه
        $this->dispatch('refreshForm');
    }

    protected function handleResetStep()
    {
        // استفاده از مقدار ذخیره شده در جلسه یا متغیر کلاس
        $mobile = session('reset_password_mobile', $this->mobile);

        $user = User::where('mobile', $mobile)->first();

        if (!$user) {
            Notification::make()
                ->title('خطا')
                ->body('کاربر یافت نشد.')
                ->danger()
                ->send();
            return;
        }

        $user->update([
            'password' => Hash::make($this->data['password']),
        ]);

        // پاک کردن داده‌های جلسه
        session()->forget('reset_password_mobile');

        // ورود خودکار کاربر
        auth()->login($user);

        // نمایش پیام موفقیت
        Notification::make()
            ->title('رمز عبور با موفقیت تغییر کرد')
            ->body('شما با موفقیت وارد سیستم شدید.')
            ->success()
            ->send();

        // هدایت به داشبورد
        $this->redirect(Filament::getUrl());
    }

    public function getTitle(): string | Htmlable
    {
        return match ($this->step) {
            'mobile' => 'فراموشی رمز عبور',
            'otp' => 'تایید کد',
            'reset' => 'تنظیم رمز عبور جدید',
        };
    }

    public function getDescription(): string | Htmlable | null
    {
        return match ($this->step) {
            'mobile' => 'لطفاً شماره موبایل خود را وارد کنید تا کد تایید برای شما ارسال شود.',
            'otp' => 'کد تایید به شماره موبایل شما ارسال شد. لطفاً آن را وارد کنید.',
            'reset' => 'لطفاً رمز عبور جدید خود را وارد کنید.',
        };
    }

    // متد برای استفاده در بخش اعتبارسنجی
    public function rules()
    {
        return match ($this->step) {
            'mobile' => [
                'data.mobile' => ['required', 'regex:/^09[0-9]{9}$/'],
            ],
            'otp' => [
                'data.otp' => ['required', 'numeric'],
            ],
            'reset' => [
                'data.password' => ['required', 'min:8'],
                'data.passwordConfirmation' => ['required', 'same:data.password'],
            ],
            default => [],
        };
    }

    // اضافه کردن متد mount برای اطمینان از مقداردهی اولیه صحیح
    public function mount(): void
    {
        parent::mount();

        $this->step = 'mobile';
        $this->form->fill();
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
