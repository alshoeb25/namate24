<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case INITIATED = 'initiated';
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::INITIATED => 'Payment Initiated',
            self::PENDING => 'Payment Pending',
            self::PROCESSING => 'Processing',
            self::SUCCESS => 'Paid',
            self::FAILED => 'Payment Failed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function isSuccess(): bool
    {
        return $this === self::SUCCESS;
    }

    public function isFailure(): bool
    {
        return in_array($this, [self::FAILED, self::CANCELLED]);
    }

    public function isPending(): bool
    {
        return in_array($this, [self::INITIATED, self::PENDING, self::PROCESSING]);
    }
}
