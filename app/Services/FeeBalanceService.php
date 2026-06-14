<?php

namespace App\Services;

use App\Models\FeeAssignment;

class FeeBalanceService
{
    public function refresh(FeeAssignment $assignment): FeeAssignment
    {
        $paid = (float) $assignment->payments()->sum('payment_amount');
        $assigned = (float) $assignment->assigned_amount;
        $balance = max(0, $assigned - $paid);

        $paymentStatus = match (true) {
            $balance <= 0 => 'paid',
            $assignment->due_date->isPast() => 'overdue',
            $paid > 0 => 'partially_paid',
            default => 'unpaid',
        };

        $assignment->update([
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'payment_status' => $paymentStatus,
        ]);

        return $assignment->fresh();
    }

    public function refreshOpenAssignments(): void
    {
        FeeAssignment::query()
            ->where('payment_status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->update(['payment_status' => 'overdue']);
    }
}
