<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * User model for authentication identities and RBAC ownership.
 */
final class User extends BaseModel
{
    protected string $table = 'users';

    /** @var array<int, string> */
    protected array $fillable = [
        'username',
        'email',
        'password_hash',
        'user_type',
        'status',
        'last_login_at',
        'email_verified_at',
    ];

    /**
     * Finds a user by username or email for future login flows.
     *
     * @return array<string, mixed>|null
     */
    public function findForLogin(string $identifier): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE username = :identifier OR email = :identifier LIMIT 1',
            ['identifier' => $identifier]
        );
    }
}
