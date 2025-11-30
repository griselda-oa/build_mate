<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Premium Subscription model
 */
class PremiumSubscription extends Model
{
    protected string $table = 'premium_subscriptions';
    
    /**
     * Create subscription record
     */
    public function createSubscription(int $supplierId, string $paymentReference, int $amountCents, string $currency = 'GHS', int $days = 30): int
    {
        return $this->create([
            'supplier_id' => $supplierId,
            'payment_reference' => $paymentReference,
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'plan_duration_days' => $days,
            'status' => 'pending'
        ]);
    }
    
    /**
     * Mark subscription as completed
     */
    public function markCompleted(int $id, string $paystackReference): bool
    {
        return $this->update($id, [
            'status' => 'completed',
            'paystack_reference' => $paystackReference
        ]);
    }
    
    /**
     * Get subscription by payment reference
     */
    public function findByPaymentReference(string $paymentReference): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE payment_reference = ?
            LIMIT 1
        ");
        $stmt->execute([$paymentReference]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get subscriptions by supplier
     */
    public function getBySupplier(int $supplierId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE supplier_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll();
    }
}

