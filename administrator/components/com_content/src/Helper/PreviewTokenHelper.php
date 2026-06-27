<?php

/**
 * @package         Joomla.Administrator
 * @subpackage      com_content
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class for generating and validating article preview tokens.
 *
 * Tokens are URL-safe, HMAC-SHA256 signed, and time-limited.
 * They allow unauthenticated public access to article previews without
 * requiring a frontend login or published state.
 *
 * Usage:
 *   $helper = new PreviewTokenHelper($app->get('secret'));
 *   $token  = $helper->createToken($articleId, 24);
 *   $valid  = $helper->validateToken($token, $articleId);
 *
 * @since  __DEPLOY_VERSION__
 */
class PreviewTokenHelper
{
    /**
     * The secret key used to sign and verify preview tokens.
     *
     * @var string
     */
    private string $secret;

    /**
     * @param   string  $secret  The secret key
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * Generate a signed preview token for a given article.
     *
     * @param   int  $id              The article ID.
     * @param   int  $expiresInHours  Number of hours before the token expires.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function createToken(int $id, int $expiresInHours): string
    {
        $payload = $this->encode(json_encode([
            'id'  => $id,
            'exp' => time() + ($expiresInHours * 3600),
        ]));

        $signature = $this->encode(hash_hmac('sha256', $payload, $this->secret, true));

        return $payload . '.' . $signature;
    }

    /**
     * Validate a preview token against a given article ID.
     *
     * Checks that the token:
     * - Has a valid structure
     * - Has not been tampered with (HMAC signature check)
     * - Has not expired
     * - Belongs to the expected article
     *
     * @param   string  $token  The preview token from the URL.
     * @param   int     $id     The expected article ID.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function validateToken(string $token, int $id): bool
    {
        $parts = explode('.', $token);

        if (\count($parts) !== 2) {
            return false;
        }

        [$payload, $signature] = $parts;

        if (!hash_equals($this->encode(hash_hmac('sha256', $payload, $this->secret, true)), $signature)) {
            return false;
        }

        $decoded = $this->decode($payload);

        if ($decoded === false) {
            return false;
        }

        $data = json_decode($decoded, true);

        if (!\is_array($data) || !isset($data['id'], $data['exp'])) {
            return false;
        }

        if (!\is_int($data['id']) || !\is_int($data['exp'])) {
            return false;
        }

        if ($data['id'] !== $id) {
            return false;
        }

        return time() < $data['exp'];
    }

    /**
     * Encode binary data to URL-safe Base64 string.
     *
     * @param   string  $data
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function encode(string $data): string
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }

    /**
     * Decode a URL-safe Base64 string to binary data.
     *
     * @param   string  $data
     *
     * @return  string|false
     *
     * @since   __DEPLOY_VERSION__
     */
    private function decode(string $data): string|false
    {
        if ($remainder = \strlen($data) % 4) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
