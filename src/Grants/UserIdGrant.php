<?php

namespace Oelmenshawy\UserIdGrant\Grants;

use League\OAuth2\Server\RequestEvent;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Grant\AbstractGrant;
use Laravel\Passport\Bridge\User as UserEntity;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Oelmenshawy\UserIdGrant\Resolvers\UserResolverInterface;

class UserIdGrant extends AbstractGrant
{
    /**
     * User resolver instance.
     *
     * @var UserResolverInterface
     */
    protected $resolver;

    /**
     * UserIdGrant constructor.
     *
     * @param UserResolverInterface $resolver
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     */
    public function __construct(
        UserResolverInterface $resolver,
        RefreshTokenRepositoryInterface $refreshTokenRepository
    ) {
        $this->resolver = $resolver;
        $this->setRefreshTokenRepository($refreshTokenRepository);

        $this->refreshTokenTTL = new \DateInterval('P1M');
    }

    /**
     * {@inheritdoc}
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ): ResponseTypeInterface {
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request);

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());

        // Issue and persist new tokens
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $finalizedScopes);
        $refreshToken = $this->issueRefreshToken($accessToken);

        // Send events to emitter
        $this->getEmitter()->emit(new RequestEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request));
        $this->getEmitter()->emit(new RequestEvent(RequestEvent::REFRESH_TOKEN_ISSUED, $request));

        // Inject tokens into response
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    /**
     * Validate server request and get the user entity.
     *
     * @param ServerRequestInterface $request
     *
     * @throw OAuthServerException
     *
     * @return UserEntity
     */
    public function validateUser(ServerRequestInterface $request): UserEntity
    {
        $user_id = $this->getRequestParameter('user_id', $request);
        if (is_null($user_id)) {
            throw OAuthServerException::invalidRequest('user_id');
        }

        $user = $this->resolver->resolveUserByUserId($user_id);
        if (is_null($user)) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));
            throw OAuthServerException::invalidCredentials();
        }

        return new UserEntity($user->getAuthIdentifier());
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'user_id';
    }
}
