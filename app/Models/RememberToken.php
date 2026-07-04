<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Persistent remember-me token model.
 */
final class RememberToken extends BaseModel
{
    protected string $table = 'remember_tokens';

    /** @var array<int, string> */
    protected array $fillable = [
        'user_id',
        'selector',
        'token_hash',
        'expires_at',
        'last_used_at',
        'created_at',
    ];
}