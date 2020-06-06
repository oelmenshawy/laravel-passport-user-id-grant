<?php

namespace Oelmenshawy\UserIdGrant\Tests;

use League\OAuth2\Server\CryptKey;
use Zend\Diactoros\ServerRequest;
use Oelmenshawy\UserIdGrant\Tests\Stubs\User;
use League\OAuth2\Server\Exception\OAuthServerException;
use Oelmenshawy\UserIdGrant\Grants\UserIdGrant;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use Oelmenshawy\UserIdGrant\Tests\Stubs\ScopeEntity;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Oelmenshawy\UserIdGrant\Tests\Stubs\ClientEntity;
use Oelmenshawy\UserIdGrant\Tests\Stubs\ResponseType;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Oelmenshawy\UserIdGrant\Tests\Stubs\AccessTokenEntity;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Oelmenshawy\UserIdGrant\Tests\Stubs\RefreshTokenEntity;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Oelmenshawy\UserIdGrant\Resolvers\UserResolverInterface;

class UserIdGrantTest extends AbstractTestCase
{
    const DEFAULT_SCOPE = 'default_scope';

    public function test_get_identifier()
    {
        $userResolverMock = $this->getMockBuilder(UserResolverInterface::class)->getMock();
        $refreshTokenRepositoryMock = $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock();

        $userIdGrant = new UserIdGrant($userResolverMock, $refreshTokenRepositoryMock);
        $this->assertEquals('user_id', $userIdGrant->getIdentifier());
    }

    public function test_respond_to_request()
    {
        $client = new ClientEntity();
        $clientRepositoryMock = $this->getMockBuilder(ClientRepositoryInterface::class)->getMock();
        $clientRepositoryMock->method('getClientEntity')->willReturn($client);

        $accessTokenRepositoryMock = $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock();
        $refreshTokenEntity = new AccessTokenEntity();
        $accessTokenRepositoryMock->method('getNewToken')->willReturn($refreshTokenEntity);
        $accessTokenRepositoryMock->method('persistNewAccessToken')->willReturnSelf();

        $userResolverMock = $this->getMockBuilder(UserResolverInterface::class)->getMock();
        $user = new User();
        $userResolverMock->method('resolveUserByProviderCredentials')->willReturn($user);

        $refreshTokenRepositoryMock = $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock();
        $refreshTokenRepositoryMock->method('persistNewRefreshToken')->willReturnSelf();
        $refreshTokenEntity = new RefreshTokenEntity();
        $refreshTokenRepositoryMock->method('getNewRefreshToken')->willReturn($refreshTokenEntity);

        $scope = new ScopeEntity();
        $scopeRepositoryMock = $this->getMockBuilder(ScopeRepositoryInterface::class)->getMock();
        $scopeRepositoryMock->method('getScopeEntityByIdentifier')->willReturn($scope);
        $scopeRepositoryMock->method('finalizeScopes')->willReturnArgument(0);

        $grant = new UserIdGrant($userResolverMock, $refreshTokenRepositoryMock);
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setScopeRepository($scopeRepositoryMock);
        $grant->setDefaultScope(self::DEFAULT_SCOPE);
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/Stubs/private.key', null, false));

        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id' => 'client_id_value',
                'client_secret' => 'client_secret_value',
                'user_id' => 'user_id_value',
            ]
        );

        $responseType = new ResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));

        $this->assertInstanceOf(AccessTokenEntityInterface::class, $responseType->getAccessToken());
        $this->assertInstanceOf(RefreshTokenEntityInterface::class, $responseType->getRefreshToken());
    }

    public function test_respond_to_request_missing_user_id()
    {
        $this->expectException(OAuthServerException::class);

        $client = new ClientEntity();
        $clientRepositoryMock = $this->getMockBuilder(ClientRepositoryInterface::class)->getMock();
        $clientRepositoryMock->method('getClientEntity')->willReturn($client);

        $accessTokenRepositoryMock = $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock();

        $userResolverMock = $this->getMockBuilder(UserResolverInterface::class)->getMock();

        $refreshTokenRepositoryMock = $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock();

        $grant = new UserIdGrant($userResolverMock, $refreshTokenRepositoryMock);
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);

        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id' => 'client_id_value',
                'client_secret' => 'client_secret_value',
            ]
        );

        $responseType = new ResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }

    public function test_respond_to_bad_credentials()
    {
        $this->expectException(OAuthServerException::class);

        $client = new ClientEntity();
        $clientRepositoryMock = $this->getMockBuilder(ClientRepositoryInterface::class)->getMock();
        $clientRepositoryMock->method('getClientEntity')->willReturn($client);

        $accessTokenRepositoryMock = $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock();

        $userResolverMock = $this->getMockBuilder(UserResolverInterface::class)->getMock();
        $user = null;
        $userResolverMock->method('resolveUserByProviderCredentials')->willReturn($user);

        $refreshTokenRepositoryMock = $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock();

        $grant = new UserIdGrant($userResolverMock, $refreshTokenRepositoryMock);
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);

        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id' => 'client_id_value',
                'client_secret' => 'client_secret_value',
                'user_id' => 'user_id_value',
            ]
        );

        $responseType = new ResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }
}
