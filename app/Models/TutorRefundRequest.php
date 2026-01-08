<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorRefundRequest extends Model
{
    protected $table = 'tutor_refund_requests';

    protected $fillable = [
        'tutor_id',
        'enquiry_id',
        'unlock_id',
        'reason',
        'status',
        'refund_amount',
        'notes',
        'admin_notes',
        'reviewed_by',
        'requested_at',
        'reviewed_at',
        'processed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSED = 'processed';

    // Reason constants
    const REASON_SPAM = 'spam';
    const REASON_NO_RESPONSE = 'no_response';
    const REASON_WRONG_DETAILS = 'wrong_details';
    const REASON_OTHER = 'other';

    public static function getReasons(): array
    {
        return [
            self::REASON_SPAM => 'Student not responding / Marked as spam',
            self::REASON_NO_RESPONSE => 'No response from student',
            self::REASON_WRONG_DETAILS => 'Enquiry details were incorrect',
            self::REASON_OTHER => 'Other reason',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PROCESSED => 'Processed',
        ];
    }

    /**
     * Tutor who requested refund
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    /**
     * Enquiry/requirement associated with refund
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(StudentRequirement::class, 'enquiry_id');
    }

    /**
     * The specific unlock record
     */
    public function unlock(): BelongsTo
    {
        return $this->belongsTo(EnquiryUnlock::class, 'unlock_id');
    }

    /**
     * Admin who reviewed this request
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope to get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to get requests for a tutor
     */
    public function scopeForTutor($query, $tutorId)
    {
        return $query->where('tutor_id', $tutorId);
    }

    /**
     * Check if request can be approved
     */
    public function canApprove(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request can be rejected
     */
    public function canReject(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get reason label
     */
    public function getReasonLabel(): string
    {
        $reasons = self::getReasons();
        return $reasons[$this->reason] ?? $this->reason;
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        $statuses = self::getStatuses();
        return $statuses[$this->status] ?? $this->status;
    }
}
