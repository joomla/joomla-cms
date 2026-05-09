<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

use Joomla\CMS\Http\HttpFactory;
use PHPMailer\PHPMailer\OAuthTokenProvider;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Generic OAuth token provider for SMTP XOAUTH2 authentication.
 *
 * @since  __DEPLOY_VERSION__
 */
final class SmtpOAuth2TokenProvider implements OAuthTokenProvider
{
    private string $tokenUrl;
    private string $scope;
    private string $clientId;
    private string $clientSecret;
    private string $refreshToken;
    private string $userName;
    private ?string $accessToken = null;
    private int $expiresAt = 0;

    public function __construct(
        string $tokenUrl,
        string $scope,
        string $clientId,
        string $clientSecret,
        string $refreshToken,
        string $userName
    ) {
        $this->tokenUrl      = $tokenUrl;
        $this->scope         = $scope;
        $this->clientId      = $clientId;
        $this->clientSecret  = $clientSecret;
        $this->refreshToken  = $refreshToken;
        $this->userName      = $userName;
    }

    public function getOauth64(): string
    {
        if (!$this->accessToken || time() >= $this->expiresAt) {
            $this->requestAccessToken();
        }

        return base64_encode('user=' . $this->userName . "\001auth=Bearer " . $this->accessToken . "\001\001");
    }

    private function requestAccessToken(): void
    {
        $http = HttpFactory::getHttp();
        $body = http_build_query(
            array_filter(
                [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
                'grant_type'    => 'refresh_token',
                'scope'         => $this->scope,
                ],
                static fn ($value) => $value !== ''
            )
        );

        $response = $http->post($this->tokenUrl, $body, ['Content-Type' => 'application/x-www-form-urlencoded']);
        $data     = json_decode((string) $response->body, true);

        if (!\is_array($data) || empty($data['access_token'])) {
            throw new \RuntimeException('Failed to acquire SMTP OAuth2 access token.');
        }

        $this->accessToken = (string) $data['access_token'];
        $expiresIn         = isset($data['expires_in']) ? (int) $data['expires_in'] : 3600;
        $this->expiresAt   = time() + max(60, $expiresIn - 60);
    }
}
