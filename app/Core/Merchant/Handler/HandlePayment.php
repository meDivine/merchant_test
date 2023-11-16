<?php
namespace App\Core\Merchant\Handler;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HandlePayment {
    private $_merchantId;
    private $_paymentId;
    private $_status;
    private $_amount;
    private $_amountPayment;
    private $_timestamp;
    // По идее у нас лимиты должны быть указаны в бд или в другом хранилище
    private int $_firstLimit = 1000;
    private int $_secondLimit = 1000;
    // Это можно хранить в .env если статические данные или же в бд если у юзеры создают свои мерчанты, все зависит от проекта и его задач
    private string $_appKey = "rTaasVHeteGbhwBx";
    private string $_merchantKey = "KaTf5tZYHx4v7pgZ";


    public function __construct($merchantId, $paymentId, $status, $amount, $amountPayment, $timestamp) {
        $this->_merchantId = $merchantId;
        $this->_paymentId = $paymentId;
        $this->_status = $status;
        $this->_amount = $amount;
        $this->_amountPayment = $amountPayment;
        $this->_timestamp = $timestamp;
    }

    private function checkFirstLimit(): bool
    {
        $sumToday = Payment::query()
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');
        return $sumToday < $this->_firstLimit;
    }

    private function checkSecondLimit(): bool
    {
        $sumToday = Payment::query()
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');
        return $sumToday < $this->_secondLimit;
    }


    private function buildFirstMerchantArray(): array
    {
        $payload = [
            'merchant_id' => $this->_merchantId,
            'payment_id' => $this->_paymentId,
            'status' => $this->_status,
            'amount' => $this->_amount,
            'timestamp' => $this->_timestamp,
            'amount_paid' => $this->_amountPayment,
        ];
        $sign = hash('sha256', implode('.', $payload) . $this->_merchantKey);
        ksort($payload); // сортировка ключей массива, можно сортировать по значениею через asort
        $payload['sign'] = $sign;
        return $payload;
    }

    private function buildSecondMerchantArray(): array
    {
        $payload = [
            'project' => $this->_merchantId,
            'invoice' => $this->_paymentId,
            'status' => $this->_status,
            'amount' => $this->_amount,
            'amount_paid' => $this->_amountPayment,
            'rand' => Str::random(10),
        ];
        $rand = hash('md5', implode('.', $payload) . $this->_appKey); // в тз не написано вставлять хэш или нет :) или же это в Заголовок авторизации
        ksort($payload); // сортировка ключей массива, можно сортировать по значениею через asort
        return $payload;
    }

    public function handle(): void
    {
        /**
         * Пишу так если простая структура if else
         * Для более читаемого кода или доп условий лучше обычные if/else
         */
        $this->checkFirstLimit() ? Http::post("url", $this->buildFirstMerchantArray()) : "Ответ если лимит выше";
        $this->checkSecondLimit() ? Http::withToken('Authorization', 'd84eb9036bfc2fa7f46727f101c73c73')
            ->post("url", $this->buildSecondMerchantArray()) : "Ответ если лимит выше";
    }
}
