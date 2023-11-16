<?php

namespace App\Observers;

use App\Core\Merchant\Handler\HandlePayment;
use App\Models\Payment;

class PaymentsObserver
{
    /**
     * Handle the Payments "created" event.
     */
    public function created(Payment $payments): void
    {
        //
    }

    /**
     * Handle the Payments "updated" event.
     */
    public function updated(Payment $payments): void
    {
        $handler = new HandlePayment($payments->merchant_id, $payments->payment_id, $payments->status, $payments->amount, $payments->amount_payment, time());
        $handler->handle();
    }

    /**
     * Handle the Payments "deleted" event.
     */
    public function deleted(Payment $payments): void
    {
        //
    }

    /**
     * Handle the Payments "restored" event.
     */
    public function restored(Payment $payments): void
    {
        //
    }

    /**
     * Handle the Payments "force deleted" event.
     */
    public function forceDeleted(Payment $payments): void
    {
        //
    }
}
