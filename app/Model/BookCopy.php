<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCopy extends Model
{
    public $timestamps = false;
    protected $table = 'books_copies';

    protected $fillable = [
        'inventory_number',
        'storage_place_id',
        'status_id',
        'book_id',
        'barcode',
        'qr_code',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(BookCopyStatus::class, 'status_id');
    }

    public function storagePlace(): BelongsTo
    {
        return $this->belongsTo(StoragePlace::class, 'storage_place_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'book_copy_id');
    }
}
