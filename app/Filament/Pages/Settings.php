<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements Forms\Contracts\HasForms
{
    use HasPageShield;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'مدیریت سیستم';
    protected static ?int $navigationSort = 90;
    protected static ?string $title = 'تنظیمات سیستم';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];
    protected $settings = [];

    public function mount(): void
    {
        $this->settings = Setting::all()->keyBy('meta_title')->toArray();

        // پر کردن داده‌های فرم بر اساس تنظیمات موجود
        $formData = [];
        foreach ($this->settings as $key => $setting) {
            $formData[$key] = $setting['meta_value'];
        }

        $this->form->fill($formData);
    }

    public function form(Form $form): Form
    {
        $formSchema = [];
        $this->settings = Setting::all()->keyBy('meta_title')->toArray();

        // گروه‌بندی تنظیمات بر اساس موضوع
        $generalSettings = [];
        $paymentSettings = [];
        $systemSettings = [];

        foreach ($this->settings as $key => $setting) {
            $field = $this->getFormField($key, $setting);

            // دسته‌بندی تنظیمات
            if (str_starts_with($key, 'consultant_') || str_starts_with($key, 'marketer_')) {
                $paymentSettings[] = $field;
            } elseif (str_starts_with($key, 'site_') || str_starts_with($key, 'admin_')) {
                $generalSettings[] = $field;
            } else {
                $systemSettings[] = $field;
            }
        }

        // اضافه کردن بخش‌های فرم
        if (!empty($generalSettings)) {
            $formSchema[] = Forms\Components\Section::make('تنظیمات عمومی')
                ->schema($generalSettings)
                ->columns(2);
        }

        if (!empty($paymentSettings)) {
            $formSchema[] = Forms\Components\Section::make('تنظیمات پرداخت و کارمزد')
                ->schema($paymentSettings)
                ->columns(2);
        }

        if (!empty($systemSettings)) {
            $formSchema[] = Forms\Components\Section::make('تنظیمات سیستم')
                ->schema($systemSettings)
                ->columns(2);
        }

        return $form
            ->schema($formSchema)
            ->statePath('data');
    }

    protected function getFormField($key, $setting): Forms\Components\Component
    {
        // ایجاد فیلد مناسب بر اساس نوع تنظیم
        return match($setting['type']) {
            'boolean' => Forms\Components\Toggle::make($key)
                ->label($setting['description'])
                ->helperText('این تنظیم به صورت بله/خیر است'),

            'number' => Forms\Components\TextInput::make($key)
                ->label($setting['description'])
                ->numeric()
                ->helperText('این تنظیم باید عدد باشد'),

            'select' => Forms\Components\Select::make($key)
                ->label($setting['description'])
                ->options($setting['options'] ?? []),

            default => Forms\Components\TextInput::make($key)
                ->label($setting['description'])
                ->helperText('متن را وارد کنید')
        };
    }

    public function save(): void
    {
        // ذخیره تنظیمات
        $formData = $this->form->getState();

        foreach ($formData as $key => $value) {
            if (isset($this->settings[$key])) {
                Setting::set($key, $value);
            }
        }

        Notification::make()
            ->title('تنظیمات ذخیره شد')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Actions\Action::make('save')
                ->label('ذخیره تنظیمات')
                ->submit('save')
                ->color('primary'),
        ];
    }
}
