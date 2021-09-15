# Teamwork Provider for PHP OAuth 2.0 Client

This package provides Teamwork OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client). Work with Teamwork API (v2). Initially, this module was used for the integration of [Bybrand](https://www.bybrand.io) with Teamwork Desk and is in production.

Full documentation, can be see in [Teamwork documentation](https://developer.teamwork.com/). Also, you can read the [App Login Flow](https://developer.teamwork.com/guides/how-to-authenticate-via-app-login-flow/).

## Installation
```
composer require bybrand/oauth2-teamwork
```

## Usage
This is a instruction base to get the token, and in then, to save in your database to future request.

```
use Bybrand\OAuth2\Client\Provider\Teamwork as ProviderTeamwork;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

$params = $_GET;

$provider = new ProviderTeamwork([
    'clientId'     => 'key-id',
    'clientSecret' => 'secret-key',
    'redirectUri'  => 'your-url-redirect'
]);

if (!isset($params['code']) or empty($params['code'])) {
    // If we don't have an authorization code then get one
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get state and store it to the session
    $_SESSION['oauth2state'] = $provider->getState();

    header('Location: '.$authorizationUrl);
    exit;
// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($params['state']) || ($params['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);

    // Set error and redirect.
    echo 'Invalid stage';
} else {
    try {
        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $params['code']
        ]);
    } catch (IdentityProviderException $e) {
        // Error, HTTP code status.
    } catch (\Exception $e) {
        // Error, make redirect or message.
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```
Please, for more information see the PHP League's general usage examples.

## Refreshing a Token
Teamwork tokens do not expire and do not need to be refreshed.

## Testing

```
bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](https://github.com/bybrand/oauth2-teamwork/blob/master/LICENSE) for more information.
