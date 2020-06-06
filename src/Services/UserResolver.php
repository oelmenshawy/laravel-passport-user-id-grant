<?php

namespace Oelmenshawy\UserIdGrant\Services;

use App\User;
use Oelmenshawy\UserIdGrant\Resolvers\UserResolverInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class UserResolver implements UserResolverInterface
{
    /**
     * Resolve user by user id.
     *
     * @param int $user_id
     * @return Authenticatable|null
     */
    public function resolveUserByUserId(int $user_id): ?Authenticatable
    {
        return User::find($user_id);
    }
}
