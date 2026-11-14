<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResidentPaymentSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $payment = $this->payment->loadMissing(['user', 'waterBill']);
        $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $period = (string) $payment->waterBill?->billing_period;
        $month = (int) substr($period, -2);

        return [
            'title' => 'Pembayaran menunggu konfirmasi',
            'message' => ($payment->user?->name ?? 'Warga') . ' telah melakukan pembayaran tagihan bulan ' . ($monthNames[$month] ?? $period) . '.',
            'payment_id' => $payment->id,
            'water_bill_id' => $payment->water_bill_id,
            'billing_period' => $period,
            'amount' => (float) $payment->amount,
        ];
    }
}
