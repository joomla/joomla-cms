<?php
namespace Kunnu\Dropbox\Security;

use Kunnu\Dropbox\Exceptions\DropboxClientException;

/**
 * @inheritdoc
 */
class OpenSslRandomStringGenerator implements RandomStringGeneratorInterface
{
    use RandomStringGeneratorTrait;

    /**
     * The error message when generating the string fails.
     *
     * @const string
     */
    const ERROR_MESSAGE = 'Unable to generate a cryptographically secure pseudo-random string from openssl_random_pseudo_bytes(). ';

    /**
     * Create a new OpenSslRandomStringGenerator instance
     *
     * @throws \Kunnu\Dropbox\Exceptions\DropboxClientException
     */
    public function __construct()
    {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            throw new DropboxClientException(
                static::ERROR_MESSAGE .
                'The function openssl_random_pseudo_bytes() does not exist.'
                );
        }
    }

    /**
     * Get a randomly generated secure token
     *
     * @param  int $length Length of the string to return
     *
     * @throws \Kunnu\Dropbox\Exceptions\DropboxClientException
     *
     * @return string
     */
    public function generateString($length)
    {
        $cryptoStrong = false;
        //Create Binary String
        $binaryString = openssl_random_pseudo_bytes($length, $cryptoStrong);

        //Unable to create binary string
        if ($binaryString === false) {
            throw new DropboxClientException(static::ERROR_MESSAGE . 'openssl_random_pseudo_bytes() returned an unknown error.');
        }

        //Binary String is not cryptographically strong
        if ($cryptoStrong !== true) {
            throw new DropboxClientException(static::ERROR_MESSAGE . 'openssl_random_pseudo_bytes() returned a pseudo-random string but it was not cryptographically secure and cannot be used.');
        }

        //Convert binary to hex
        return $this->binToHex($binaryString, $length);
    }
}
