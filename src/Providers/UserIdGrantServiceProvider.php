<?php

namespace Oelmenshawy\UserIdGrant\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Oelmenshawy\UserIdGrant\Grants\UserIdGrant;
use Oelmenshawy\UserIdGrant\Resolvers\UserResolverInterface;
use Oelmenshawy\UserIdGrant\Services\UserResolver;

class UserIdGrantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(UserResolverInterface::class, UserResolver::class);

        $this->app->resolving(AuthorizationServer::class, function (AuthorizationServer $server) {
            $server->enableGrantType(
                $this->makeUserIdGrant(),
                Passport::tokensExpireIn()
            );
        });
    }

    /**
     * Create and configure a user id grant instance.
     *
     * @return UserIdGrant
     */
    protected function makeUserIdGrant(): UserIdGrant
    {
        $grant = new UserIdGrant(
            $this->app->make(UserResolverInterface::class),
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }
}
