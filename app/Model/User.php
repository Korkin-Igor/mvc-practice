<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Auth\IdentityInterface;

class User extends Model implements IdentityInterface
{
    public const ROLE_LIBRARIAN = 1;
    public const ROLE_READER = 2;

    public $timestamps = false;
    protected $table = 'users';

    protected $fillable = [
        'name',
        'login',
        'password',
        'role_id',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->role_id)) {
                $user->role_id = self::ROLE_READER;
            }
            $user->password = md5($user->password);
        });
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function readerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'reader_id');
    }

    public function approvedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'approved_by');
    }

    public function isLibrarian(): bool
    {
        return (int) $this->role_id === self::ROLE_LIBRARIAN;
    }

    public function isReader(): bool
    {
        return (int) $this->role_id === self::ROLE_READER;
    }

    //Выборка пользователя по первичному ключу
    public function findIdentity(int $id)
    {
        return self::with('role')->where('id', $id)->first();
    }

    //Возврат первичного ключа
    public function getId(): int
    {
        return $this->id;
    }
    //Возврат аутентифицированного пользователя
    public function attemptIdentity(array $credentials)
    {
        return self::with('role')->where([
            'login' => $credentials['login'] ?? '',
            'password' => md5($credentials['password'] ?? ''),
        ])->first();
    }
}
