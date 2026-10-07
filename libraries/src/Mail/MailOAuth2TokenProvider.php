<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

use Joomla\OAuth2\Client;
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
        $oauth2 = new Client([
            'tokenurl'     => $this->tokenUrl,
            'clientid'     => $this->clientId,
            'clientsecret' => $this->clientSecret,
            'userefresh'   => true,
        ]);

        $token = $oauth2->refreshToken($this->refreshToken);

        if (empty($token['access_token'])) {
            throw new \RuntimeException(
                'Failed to acquire SMTP OAuth2 access token.'
            );
        }

        $this->accessToken = (string) $token['access_token'];

        $expiresIn = isset($token['expires_in']) ? (int) $token['expires_in'] : 3600;

        $this->expiresAt = $token['created'] + max(60, $expiresIn - 60);
    }
}
