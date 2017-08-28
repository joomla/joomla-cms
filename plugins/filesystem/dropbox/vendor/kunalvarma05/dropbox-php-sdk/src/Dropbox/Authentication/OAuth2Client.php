<?php
namespace Kunnu\Dropbox\Authentication;

use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxClient;
use Kunnu\Dropbox\DropboxRequest;
use Kunnu\Dropbox\Security\RandomStringGeneratorInterface;

class OAuth2Client
{

    /**
     * The Base URL
     *
     * @const string
     */
    const BASE_URL = "https://dropbox.com";

    /**
     * Auth Token URL
     *
     * @const string
     */
    const AUTH_TOKEN_URL = "https://api.dropboxapi.com/oauth2/token";

    /**
     * The Dropbox App
     *
     * @var \Kunnu\Dropbox\DropboxApp
     */
    protected $app;

    /**
     * The Dropbox Client
     *
     * @var \Kunnu\Dropbox\DropboxClient
     */
    protected $client;

    /**
     * Random String Generator
     *
     * @var \Kunnu\Dropbox\Security\RandomStringGeneratorInterface
     */
    protected $randStrGenerator;

    /**
     * Create a new DropboxApp instance
     *
     * @param \Kunnu\Dropbox\DropboxApp $app
     * @param \Kunnu\Dropbox\DropboxClient $client
     * @param \Kunnu\Dropbox\Security\RandomStringGeneratorInterface $randStrGenerator
     */
    public function __construct(DropboxApp $app, DropboxClient $client, RandomStringGeneratorInterface $randStrGenerator = null)
    {
        $this->app = $app;
        $this->client = $client;
        $this->randStrGenerator = $randStrGenerator;
    }

    /**
     * Build URL
     *
     * @param  string $endpoint
     * @param  array  $params   Query Params
     *
     * @return string
     */
    protected function buildUrl($endpoint = '', array $params = [])
    {
        $queryParams = http_build_query($params);
        return static::BASE_URL . $endpoint . '?' . $queryParams;
    }

    /**
     * Get the Dropbox App
     *
     * @return \Kunnu\Dropbox\DropboxApp
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Get the Dropbox Client
     *
     * @return \Kunnu\Dropbox\DropboxClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the OAuth2 Authorization URL
     *
     * @param string $redirectUri Callback URL to redirect user after authorization.
     *                            If null is passed, redirect_uri will be omitted
     *                            from the url and the code will be presented directly
     *                            to the user.
     * @param string $state       CSRF Token
     * @param array  $params      Additional Params
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#oauth2-authorize
     *
     * @return string
     */
    public function getAuthorizationUrl($redirectUri = null, $state = null, array $params = [])
    {
        //Request Parameters
        $params = array_merge([
            'client_id' => $this->getApp()->getClientId(),
            'response_type' => 'code',
            'state' => $state,
            ], $params);

        if(!is_null($redirectUri)) {
            $params['redirect_uri'] = $redirectUri;
        }

        return $this->buildUrl('/oauth2/authorize', $params);
    }

    /**
     * Get Access Token
     *
     * @param  string $code        Authorization Code
     * @param  string $redirectUri Redirect URI used while getAuthorizationUrl
     * @param  string $grant_type  Grant Type ['authorization_code']
     *
     * @return array
     */
    public function getAccessToken($code, $redirectUri = null, $grant_type = 'authorization_code')
    {
        //Request Params
        $params = [
        'code' => $code,
        'grant_type' => $grant_type,
        'client_id' => $this->getApp()->getClientId(),
        'client_secret' => $this->getApp()->getClientSecret(),
        'redirect_uri' => $redirectUri
        ];

        $params = http_build_query($params);

        $apiUrl = static::AUTH_TOKEN_URL;
        $uri = $apiUrl . "?" . $params;

        //Send Request through the DropboxClient
        //Fetch the Response (DropboxRawResponse)
        $response = $this->getClient()
        ->getHttpClient()
        ->send($uri, "POST", null);

        //Fetch Response Body
        $body = $response->getBody();

        //Decode the Response body to associative array
        //and return
        return json_decode((string) $body, true);
    }

    /**
     * Disables the access token
     *
     * @return void
     */
    public function revokeAccessToken()
    {
        //Access Token
        $accessToken = $this->getApp()->getAccessToken();

        //Request
        $request = new DropboxRequest("POST", "/auth/token/revoke", $accessToken);
        // Do not validate the response
        // since the /token/revoke endpoint
        // doesn't return anything in the response.
        // See: https://www.dropbox.com/developers/documentation/http/documentation#auth-token-revoke
        $request->setParams(['validateResponse' => false]);

        //Revoke Access Token
        $this->getClient()->sendRequest($request);
    }
}
