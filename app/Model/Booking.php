<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    public $timestamps = false;
    protected $table = 'bookings';

    protected $fillable = [
        'book_copy_id',
        'reader_id',
        'approved_by',
        'status_id',
        'due_date',
        'created_at',
    ];

    public function copy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class, 'book_copy_id');
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reader_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'status_id');
    }
}
