<?php

namespace Oelmenshawy\UserIdGrant\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;

interface UserResolverInterface
{
    /**
     * Resolve user by user id.
     *
     * @param int $user_id
     * @return Authenticatable|null
     */
    public function resolveUserByUserId(int $user_id): ?Authenticatable;
}
