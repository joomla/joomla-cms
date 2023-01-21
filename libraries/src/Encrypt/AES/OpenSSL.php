<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Encrypt\AES;

use Joomla\CMS\Encrypt\Randval;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * OpenSSL encryption class
 *
 * @since   4.0.0
 */
class OpenSSL extends AbstractAES implements AesInterface
{
    /**
     * The OpenSSL options for encryption / decryption
     *
     * @var  integer
     */
    protected $openSSLOptions = 0;

    /**
     * The encryption method to use
     *
     * @var  string
     */
    protected $method = 'aes-128-cbc';

    /**
     * Constructor for this class
     */
    public function __construct()
    {
        $this->openSSLOptions = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;
    }

    /**
     * Sets the AES encryption mode.
     *
     * WARNING: The strength is deprecated as it has a different effect in MCrypt and OpenSSL. MCrypt was abandoned in
     * 2003 before the Rijndael-128 algorithm was officially the Advanced Encryption Standard (AES). MCrypt also offered
     * Rijndael-192 and Rijndael-256 algorithms with different block sizes. These are NOT used in AES. OpenSSL, however,
     * implements AES correctly. It always uses a 128-bit (16 byte) block. The 192 and 256 bit strengths refer to the
     * key size, not the block size. Therefore using different strengths in MCrypt and OpenSSL will result in different
     * and incompatible ciphertexts.
     *
     * TL;DR: Always use $strength = 128!
     *
     * @param   string  $mode      Choose between CBC (recommended) or ECB
     * @param   int     $strength  Bit strength of the key (128, 192 or 256 bits). DEPRECATED. READ NOTES ABOVE.
     *
     * @return  mixed
     */
    public function setEncryptionMode($mode = 'cbc', $strength = 128)
    {
        static $availableAlgorithms = null;
        static $defaultAlgo = 'aes-128-cbc';

        if (!\is_array($availableAlgorithms)) {
            $availableAlgorithms = openssl_get_cipher_methods();

            foreach (
                ['aes-256-cbc', 'aes-256-ecb', 'aes-192-cbc',
                'aes-192-ecb', 'aes-128-cbc', 'aes-128-ecb'] as $algo
            ) {
                if (\in_array($algo, $availableAlgorithms)) {
                    $defaultAlgo = $algo;
                    break;
                }
            }
        }

        $strength = (int) $strength;
        $mode     = strtolower($mode);

        if (!\in_array($strength, [128, 192, 256])) {
            $strength = 256;
        }

        if (!\in_array($mode, ['cbc', 'ebc'])) {
            $mode = 'cbc';
        }

        $algo = 'aes-' . $strength . '-' . $mode;

        if (!\in_array($algo, $availableAlgorithms)) {
            $algo = $defaultAlgo;
        }

        $this->method = $algo;
    }

    /**
     * Encrypts a string. Returns the raw binary ciphertext.
     *
     * WARNING: The plaintext is zero-padded to the algorithm's block size. You are advised to store the size of the
     * plaintext and trim the string to that length upon decryption.
     *
     * @param   string       $plainText  The plaintext to encrypt
     * @param   string       $key        The raw binary key (will be zero-padded or chopped if its size is different than the block size)
     * @param   null|string  $iv         The initialization vector (for CBC mode algorithms)
     *
     * @return  string  The raw encrypted binary string.
     */
    public function encrypt($plainText, $key, $iv = null)
    {
        $iv_size = $this->getBlockSize();
        $key     = $this->resizeKey($key, $iv_size);
        $iv      = $this->resizeKey($iv, $iv_size);

        if (empty($iv)) {
            $randVal   = new Randval();
            $iv        = $randVal->generate($iv_size);
        }

        $plainText .= $this->getZeroPadding($plainText, $iv_size);
        $cipherText = openssl_encrypt($plainText, $this->method, $key, $this->openSSLOptions, $iv);
        $cipherText = $iv . $cipherText;

        return $cipherText;
    }

    /**
     * Decrypts a string. Returns the raw binary plaintext.
     *
     * $ciphertext MUST start with the IV followed by the ciphertext, even for EBC data (the first block of data is
     * dropped in EBC mode since there is no concept of IV in EBC).
     *
     * WARNING: The returned plaintext is zero-padded to the algorithm's block size during encryption. You are advised
     * to trim the string to the original plaintext's length upon decryption. While rtrim($decrypted, "\0") sounds
     * appealing it's NOT the correct approach for binary data (zero bytes may actually be part of your plaintext, not
     * just padding!).
     *
     * @param   string  $cipherText  The ciphertext to encrypt
     * @param   string  $key         The raw binary key (will be zero-padded or chopped if its size is different than the block size)
     *
     * @return  string  The raw unencrypted binary string.
     */
    public function decrypt($cipherText, $key)
    {
        $iv_size    = $this->getBlockSize();
        $key        = $this->resizeKey($key, $iv_size);
        $iv         = substr($cipherText, 0, $iv_size);
        $cipherText = substr($cipherText, $iv_size);
        $plainText  = openssl_decrypt($cipherText, $this->method, $key, $this->openSSLOptions, $iv);

        // Remove the zero padding
        return rtrim($plainText, "\0");
    }

    /**
     * Is this adapter supported?
     *
     * @return  boolean
     */
    public function isSupported()
    {
        if (!\function_exists('openssl_get_cipher_methods')) {
            return false;
        }

        if (!\function_exists('openssl_random_pseudo_bytes')) {
            return false;
        }

        if (!\function_exists('openssl_cipher_iv_length')) {
            return false;
        }

        if (!\function_exists('openssl_encrypt')) {
            return false;
        }

        if (!\function_exists('openssl_decrypt')) {
            return false;
        }

        if (!\function_exists('hash')) {
            return false;
        }

        if (!\function_exists('hash_algos')) {
            return false;
        }

        $algorithms = openssl_get_cipher_methods();

        if (!\in_array('aes-128-cbc', $algorithms)) {
            return false;
        }

        $algorithms = hash_algos();

        if (!\in_array('sha256', $algorithms)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the encryption block size in bytes
     *
     * @return  integer
     */
    public function getBlockSize()
    {
        return openssl_cipher_iv_length($this->method);
    }
}
