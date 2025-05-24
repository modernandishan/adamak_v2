<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment;

class PaymentService
{
    /**
     * ایجاد تراکنش جدید برای شارژ کیف پول
     */
    public function createDepositTransaction(User $user, int $amount): Transaction
    {
        return DB::transaction(function () use ($user, $amount) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => Transaction::TYPE_DEPOSIT,
                'amount' => $amount,
                'balance_before' => $user->wallet_balance,
                'balance_after' => $user->wallet_balance, // فعلا تغییری نمی‌کند
                'payment_gateway' => Transaction::GATEWAY_ZARINPAL,
                'status' => Transaction::STATUS_PENDING,
                'description' => 'شارژ کیف پول',
            ]);

            return $transaction;
        });
    }

    /**
     * پردازش پرداخت با زرین‌پال
     */
    public function processPayment(Transaction $transaction): string
    {
        try {
            $invoice = (new Invoice)->amount($transaction->amount);

            // تنظیم درایور زرین‌پال
            $payment = Payment::via('zarinpal');

            // تنظیم callback URL برای بازگشت از درگاه
            $callbackUrl = route('filament.admin.pages.payment-callback');

            $payment->config([
                'callbackUrl' => $callbackUrl,
                'description' => 'شارژ کیف پول - ' . $transaction->user->full_name,
            ]);

            // درخواست پرداخت
            $payment->purchase($invoice, function($driver, $transactionId) use ($transaction) {
                // ذخیره شناسه تراکنش درگاه
                $transaction->update([
                    'payment_data' => array_merge($transaction->payment_data ?? [], [
                        'transaction_id' => $transactionId,
                    ])
                ]);
            });

            // دریافت URL پرداخت
            return $payment->pay()->getUrl();

        } catch (\Exception $e) {
            Log::error('Payment error: ' . $e->getMessage());

            // به‌روزرسانی وضعیت تراکنش
            $transaction->update([
                'status' => Transaction::STATUS_FAILED,
                'payment_data' => array_merge($transaction->payment_data ?? [], [
                    'error' => $e->getMessage()
                ])
            ]);

            throw $e;
        }
    }

    /**
     * تایید پرداخت
     */
    public function verifyPayment(Transaction $transaction, array $data): bool
    {
        try {
            $paymentData = $transaction->payment_data ?? [];
            $transactionId = $paymentData['transaction_id'] ?? null;

            if (!$transactionId) {
                throw new \Exception('Transaction ID not found');
            }

            // تایید پرداخت
            $receipt = Payment::via('zarinpal')
                ->amount($transaction->amount)
                ->transactionId($transactionId)
                ->verify();

            // به‌روزرسانی تراکنش و کیف پول کاربر
            DB::transaction(function () use ($transaction, $receipt) {
                $user = $transaction->user;
                $newBalance = $user->wallet_balance + $transaction->amount;

                // به‌روزرسانی موجودی کاربر
                $user->update([
                    'wallet_balance' => $newBalance
                ]);

                // به‌روزرسانی تراکنش
                $transaction->update([
                    'status' => Transaction::STATUS_COMPLETED,
                    'balance_after' => $newBalance,
                    'payment_ref_id' => $receipt->getReferenceId(),
                    'payment_data' => array_merge($transaction->payment_data ?? [], [
                        'reference_id' => $receipt->getReferenceId(),
                        'verified_at' => now()->toDateTimeString(),
                    ])
                ]);
            });

            return true;

        } catch (InvalidPaymentException $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());

            // به‌روزرسانی وضعیت تراکنش
            $transaction->update([
                'status' => Transaction::STATUS_FAILED,
                'payment_data' => array_merge($transaction->payment_data ?? [], [
                    'verification_error' => $e->getMessage()
                ])
            ]);

            return false;
        }
    }

    /**
     * لغو پرداخت
     */
    public function cancelPayment(Transaction $transaction): void
    {
        if ($transaction->status === Transaction::STATUS_PENDING) {
            $transaction->update([
                'status' => Transaction::STATUS_FAILED,
                'payment_data' => array_merge($transaction->payment_data ?? [], [
                    'cancelled_at' => now()->toDateTimeString(),
                    'cancelled_by' => 'user'
                ])
            ]);
        }
    }
}
