<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Services\PaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class WalletCharge extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'کیف پول';
    protected static ?string $title = 'شارژ کیف پول';
    protected static ?string $navigationLabel = 'شارژ کیف پول';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.wallet-charge';

    public ?array $data = [];
    protected PaymentService $paymentService;

    public function boot()
    {
        $this->paymentService = app(PaymentService::class);
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('شارژ کیف پول')
                    ->description('لطفا مبلغ مورد نظر خود را وارد کنید')
                    ->schema([
                        Forms\Components\Placeholder::make('current_balance')
                            ->label('موجودی فعلی')
                            ->content(function () {
                                return number_format(Auth::user()->wallet_balance) . ' تومان';
                            }),

                        Forms\Components\TextInput::make('amount')
                            ->label('مبلغ شارژ (تومان)')
                            ->required()
                            ->numeric()
                            ->minValue(10000)
                            ->maxValue(50000000)
                            ->step(1000)
                            ->suffix('تومان')
                            ->helperText('حداقل مبلغ شارژ 10,000 تومان و حداکثر 50,000,000 تومان می‌باشد'),

                        Forms\Components\Radio::make('predefined_amount')
                            ->label('مبالغ پیشنهادی')
                            ->options([
                                '50000' => '50,000 تومان',
                                '100000' => '100,000 تومان',
                                '200000' => '200,000 تومان',
                                '500000' => '500,000 تومان',
                                '1000000' => '1,000,000 تومان',
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('amount', $state);
                                }
                            }),
                    ]),

                Forms\Components\Section::make('توضیحات')
                    ->schema([
                        Forms\Components\Placeholder::make('info')
                            ->content('پس از کلیک بر روی دکمه پرداخت، به درگاه امن بانکی منتقل خواهید شد.')
                            ->hiddenLabel(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        try {
            // ایجاد تراکنش
            $transaction = $this->paymentService->createDepositTransaction(
                Auth::user(),
                (int) $data['amount']
            );

            // پردازش پرداخت و دریافت URL درگاه
            $paymentUrl = $this->paymentService->processPayment($transaction);

            // ذخیره ID تراکنش در session
            session(['payment_transaction_id' => $transaction->id]);

            // انتقال به درگاه پرداخت
            $this->redirect($paymentUrl);

        } catch (\Exception $e) {
            Notification::make()
                ->title('خطا در پردازش درخواست')
                ->body('لطفا مجددا تلاش کنید.')
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Actions\Action::make('submit')
                ->label('پرداخت')
                ->submit('submit')
                ->color('success')
                ->icon('heroicon-m-credit-card')
                ->size('lg'),
        ];
    }
}
