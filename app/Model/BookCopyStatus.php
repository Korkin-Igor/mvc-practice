<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCopyStatus extends Model
{
    public $timestamps = false;
    protected $table = 'books_copies_statuses';

    protected $fillable = [
        'name',
    ];

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'status_id');
    }
}
