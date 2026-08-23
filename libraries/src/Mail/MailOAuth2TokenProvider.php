<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

use Joomla\Http\HttpFactory;
use PHPMailer\PHPMailer\OAuthTokenProvider;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Generic OAuth token provider for SMTP XOAUTH2 authentication.
 *
 * @since  __DEPLOY_VERSION__
 */
final class MailOAuth2TokenProvider implements OAuthTokenProvider
{
    /**
     * @var string|null Access token
     */
    private ?string $accessToken = null;

    /** @var int Expiration time */
    private int $expiresAt = 0;

    public function __construct(
        private string $tokenUrl,
        private string $scope,
        private string $clientId,
        private string $clientSecret,
        private string $refreshToken,
        private string $userName
    ) {
    }

    /**
     * Generate a base64-encoded OAuth token ensuring that the access token has not expired.
     * The string to be base 64 encoded should be in the form:
     * "user=<user_email_address>\001auth=Bearer <access_token>\001\001"
     *
     * @return string
     */
    public function getOauth64(): string
    {
        if (!$this->accessToken || time() >= $this->expiresAt) {
            $this->requestAccessToken();
        }

        return base64_encode('user=' . $this->userName . "\001auth=Bearer " . $this->accessToken . "\001\001");
    }

    /**
     * Request a new access token using the refresh token.
     *
     * @return void
     */
    private function requestAccessToken(): void
    {
        $httpFactory = new HttpFactory();
        $http        = $httpFactory->getHttp();
        $body        = http_build_query(
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
        $data     = json_decode((string) $response->getBody(), true);

        if (!\is_array($data) || empty($data['access_token'])) {
            throw new \RuntimeException('Failed to acquire SMTP OAuth2 access token.');
        }

        $this->accessToken = (string) $data['access_token'];
        $expiresIn         = isset($data['expires_in']) ? (int) $data['expires_in'] : 3600;
        $this->expiresAt   = time() + max(60, $expiresIn - 60);
    }
}
