<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'invoice_number',
        'amount',
        'currency',
        'coins',
        'bonus_coins',
        'razorpay_order_id',
        'razorpay_payment_id',
        'pdf_path',
        'status',
        'meta',
        'issued_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the invoice
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order for this invoice
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Count invoices this month
        $count = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        
        return sprintf('INV-%s-%s-%04d', $year, $month, $count);
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Get PDF download URL
     */
    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }
}
