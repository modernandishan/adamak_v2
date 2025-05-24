<?php

namespace App\Filament\Pages;

use App\Models\Test;
use App\Models\TestPurchase;
use App\Models\Transaction;
use App\Services\TestPurchaseService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestDetails extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.test-details';
    protected static bool $shouldRegisterNavigation = false;

    public Test $test;
    public bool $showPurchaseModal = false;
    public bool $hasFamily = false;
    public bool $hasPurchased = false;

    public function mount(Test $test): void
    {
        $this->test = $test->load(['category', 'questions']);

        // بررسی اینکه آیا کاربر خانواده ثبت کرده است
        $this->hasFamily = Auth::user()->families()->exists();

        // بررسی اینکه آیا کاربر قبلاً این آزمون را خریداری کرده است
        $this->hasPurchased = $this->checkIfPurchased();
    }

    public function getTitle(): string
    {
        return $this->test->title;
    }

    protected function checkIfPurchased(): bool
    {
        return TestPurchase::where('user_id', Auth::id())
            ->where('test_id', $this->test->id)
            ->exists();
    }

    public function canPurchase(): bool
    {
        // اگر قبلاً خریداری کرده، نمی‌تواند دوباره بخرد
        if ($this->hasPurchased) {
            return false;
        }

        // اگر آزمون نیاز به خانواده دارد و کاربر خانواده ندارد
        if ($this->test->requires_family && !$this->hasFamily) {
            return false;
        }

        // بررسی موجودی کیف پول
        $effectivePrice = $this->test->discounted_price ?? $this->test->price;
        return Auth::user()->wallet_balance >= $effectivePrice;
    }

    public function openPurchaseModal(): void
    {
        if (!$this->canPurchase()) {
            if ($this->hasPurchased) {
                Notification::make()
                    ->title('خطا')
                    ->body('شما قبلاً این آزمون را خریداری کرده‌اید.')
                    ->danger()
                    ->send();
            } elseif ($this->test->requires_family && !$this->hasFamily) {
                Notification::make()
                    ->title('نیاز به ثبت خانواده')
                    ->body('برای شرکت در این آزمون، ابتدا باید اطلاعات خانواده خود را ثبت کنید.')
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('موجودی ناکافی')
                    ->body('موجودی کیف پول شما برای خرید این آزمون کافی نیست.')
                    ->danger()
                    ->send();
            }
            return;
        }

        $this->showPurchaseModal = true;
    }

    public function closePurchaseModal(): void
    {
        $this->showPurchaseModal = false;
    }

    public function purchase(): void
    {
        if (!$this->canPurchase()) {
            $this->closePurchaseModal();
            return;
        }

        try {
            DB::transaction(function () {
                $user = Auth::user();
                $effectivePrice = $this->test->discounted_price ?? $this->test->price;

                // کسر مبلغ از کیف پول
                $balanceBefore = $user->wallet_balance;
                $balanceAfter = $balanceBefore - $effectivePrice;

                $user->update([
                    'wallet_balance' => $balanceAfter
                ]);

                // ثبت تراکنش
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => Transaction::TYPE_PURCHASE,
                    'amount' => $effectivePrice,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'status' => Transaction::STATUS_COMPLETED,
                    'description' => 'خرید آزمون: ' . $this->test->title,
                    'payment_data' => [
                        'test_id' => $this->test->id,
                        'test_title' => $this->test->title,
                        'original_price' => $this->test->price,
                        'discounted_price' => $this->test->discounted_price,
                        'purchased_at' => now()->toDateTimeString(),
                    ]
                ]);

                // ایجاد رکورد خرید آزمون
                TestPurchase::create([
                    'user_id' => $user->id,
                    'test_id' => $this->test->id,
                    'transaction_id' => $transaction->id,
                    'amount_paid' => $effectivePrice,
                    'status' => TestPurchase::STATUS_PENDING,
                ]);

                $this->hasPurchased = true;
                $this->closePurchaseModal();

                Notification::make()
                    ->title('خرید موفق')
                    ->body('آزمون با موفقیت خریداری شد. اکنون می‌توانید در آزمون شرکت کنید.')
                    ->success()
                    ->send();

                // انتقال به صفحه شروع آزمون
                // $this->redirect(route('filament.admin.pages.test-start', ['test' => $this->test->id]));
            });
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطا در خرید')
                ->body('خطایی در فرآیند خرید رخ داد. لطفاً مجدداً تلاش کنید.')
                ->danger()
                ->send();
        }
    }
}
