<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingStatus extends Model
{
    public $timestamps = false;
    protected $table = 'bookings_statuses';

    protected $fillable = [
        'name',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'status_id');
    }
}
