<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Services\PaymentService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\Request;

class PaymentCallback extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static string $view = 'filament.pages.payment-callback';
    protected static bool $shouldRegisterNavigation = false;

    public ?Transaction $transaction = null;
    public bool $isSuccess = false;
    public string $message = '';
    protected PaymentService $paymentService;

    public function boot()
    {
        $this->paymentService = app(PaymentService::class);
    }

    public function mount(Request $request): void
    {
        // دریافت ID تراکنش از session
        $transactionId = session('payment_transaction_id');

        if (!$transactionId) {
            $this->message = 'تراکنش یافت نشد';
            return;
        }

        $this->transaction = Transaction::find($transactionId);

        if (!$this->transaction || $this->transaction->user_id !== auth()->id()) {
            $this->message = 'تراکنش نامعتبر است';
            return;
        }

        // بررسی وضعیت پرداخت
        $status = $request->get('Status');
        $authority = $request->get('Authority');

        if ($status === 'OK' && $authority) {
            // تایید پرداخت
            $this->isSuccess = $this->paymentService->verifyPayment(
                $this->transaction,
                $request->all()
            );

            if ($this->isSuccess) {
                $this->message = 'پرداخت با موفقیت انجام شد و کیف پول شما شارژ گردید.';

                Notification::make()
                    ->title('شارژ کیف پول موفق')
                    ->body('مبلغ ' . number_format($this->transaction->amount) . ' تومان به کیف پول شما اضافه شد.')
                    ->success()
                    ->send();
            } else {
                $this->message = 'خطا در تایید پرداخت. لطفا با پشتیبانی تماس بگیرید.';
            }
        } else {
            // لغو پرداخت توسط کاربر
            $this->paymentService->cancelPayment($this->transaction);
            $this->message = 'پرداخت توسط شما لغو شد.';
        }

        // پاک کردن session
        session()->forget('payment_transaction_id');
    }

    public function getTitle(): string
    {
        return $this->isSuccess ? 'پرداخت موفق' : 'پرداخت ناموفق';
    }
}
