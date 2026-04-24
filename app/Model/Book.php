<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    public $timestamps = false;
    protected $table = 'books';

    protected $fillable = [
        'name',
        'author',
        'description',
        'link',
    ];

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'book_id');
    }
}
