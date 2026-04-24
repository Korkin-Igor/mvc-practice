<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoragePlace extends Model
{
    public $timestamps = false;
    protected $table = 'storage_places';

    protected $fillable = [
        'name',
    ];

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'storage_place_id');
    }
}
