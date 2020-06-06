# Laravel Passport User Id Grant

This package adds a user id grant for your OAuth2 server. It can be useful if have an API and want to provide the ability for your users to return `access_token` and `refresh_token` through user id.

As a result you will be able to exchange user id to `access_token` and `refresh_token` issued by your own OAuth2 server. You will receive this user id and return the user instance that corresponds to it on your own.

## Installation

You can install this package via composer using this command:

```bash
composer require oelmenshawy/laravel-passport-user-id-grant
```

The package will automatically register itself.

## Usage

Example of usage with `axios`:

```javascript
axios.post('/oauth/token', {
    grant_type: 'user_id', // static 'user_id' value
    client_id: clientId, // client id
    client_secret: clientSecret, // client secret
    user_id: userId, // user id
  })
  .then((response) => {
    const {
      access_token: accessToken,
      expires_in: expiresIn,
      refresh_token: refreshToken,
    } = response.data;

    // success logic
  })
  .catch((error) => {
    const {
      message,
      hint,
    } = error.response.data;

    // error logic
  });
```

Example of usage with `guzzle`:

```php
<?php

use GuzzleHttp\Client;
use Illuminate\Support\Arr;

$http = new Client;

$response = $http->post($domain . '/oauth/token', [
    RequestOptions::FORM_PARAMS => [
        'grant_type' => 'user_id', // static 'user_id' value
        'client_id' => $clientId, // client id
        'client_secret' => $clientSecret, // client secret
        'user_id' => $userId, // user id
    ],
    RequestOptions::HTTP_ERRORS => false,
]);
$data = json_decode($response->getBody()->getContents(), true);

if ($response->getStatusCode() === Response::HTTP_OK) {
    $accessToken = Arr::get($data, 'access_token');
    $expiresIn = Arr::get($data, 'expires_in');
    $refreshToken = Arr::get($data, 'refresh_token');

    // success logic
} else {
    $message = Arr::get($data, 'message');
    $hint = Arr::get($data, 'hint');

    // error logic
}
```

## Testing

You can run the tests with:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
