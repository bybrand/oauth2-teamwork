<?php

namespace Bybrand\OAuth2\Client\Test\Provider;

use PHPUnit\Framework\TestCase;
use Mockery as m;

use Bybrand\OAuth2\Client\Provider\Teamwork;
use Bybrand\OAuth2\Client\OptionProvider\JsonAuthOptionProvider;

/**
 * @group Teamwork
 */
class TeamworkTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new Teamwork([
            'clientId'     => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri'  => 'none',
        ]);

        $this->provider->setOptionProvider(new JsonAuthOptionProvider);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @group Teamwork.authorizationUrl
     */
    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();

        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertNotNull($this->provider->getState());
    }

    /**
     * @group Teamwork.getAuthorizationUrl
     */
    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/launchpad/login', $uri['path']);
    }

    /**
     * @group Teamwork.getBaseAccessTokenUrl
     */
    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/launchpad/v1/token.json', $uri['path']);
    }

    /**
     * @group Teamwork.getAccessToken
     */
    public function testGetAccessToken()
    {
        $json = $this->jsonReturn();

        $mockResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $mockResponse->shouldReceive('getBody')->andReturn(json_encode($json));
        $mockResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $mockResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($mockResponse);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('client_credentials', [
            'code' => 'mock_authorization_code'
        ]);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
    }

    /**
     * @group Teamwork.installation
     */
    public function testInstallation()
    {
        $json = $this->jsonReturn();

        $mockResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $mockResponse->shouldReceive('getBody')->andReturn(json_encode($json));
        $mockResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $mockResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($mockResponse);

        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('client_credentials', [
            'code' => 'mock_authorization_code'
        ]);

        $installation = $token->getValues()['installation'];

        $this->assertEquals('1', $installation['id']);
        $this->assertEquals('Teamwork Developer', $installation['name']);
        $this->assertEquals('http://mock.teamwork.com/', $installation['apiEndPoint']);
        $this->assertEquals('US', $installation['region']);
    }

    private function jsonReturn(): array
    {
        return [
            'access_token' => 'mock_access_token',
            'status'=> 'ok',
            'installation' => [
                'id' => 1,
                'name' => 'Teamwork Developer',
                'region' => 'US',
                'apiEndPoint' => 'http://mock.teamwork.com/',
                'url' => 'http://mock.teamwork.com/',
                'chatEnabled' => false,
                'company' => [
                    'id'   => 1,
                    'name' => 'Teamwork',
                    'logo' => 'URL'
                ],
                'projectsEnabled' => true
            ],
            'user' => []
        ];
    }
}
