<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt\Cipher;

use Joomla\Crypt\CipherInterface;
use Joomla\Crypt\Key;
use ParagonIE\Sodium\Compat;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JCrypt cipher for sodium algorithm encryption, decryption and key generation.
 *
 * @since  3.8.0
 */
class SodiumCipher implements CipherInterface
{
    /**
     * The message nonce to be used with encryption/decryption
     *
     * @var    string
     * @since  3.8.0
     */
    private $nonce;

    /**
     * Method to decrypt a data string.
     *
     * @param   string  $data  The encrypted string to decrypt.
     * @param   Key     $key   The key object to use for decryption.
     *
     * @return  string  The decrypted data string.
     *
     * @since   3.8.0
     * @throws  \RuntimeException
     */
    public function decrypt($data, Key $key)
    {
        // Validate key.
        if ($key->getType() !== 'sodium') {
            throw new \InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected sodium.');
        }

        if (!$this->nonce) {
            throw new \RuntimeException('Missing nonce to decrypt data');
        }

        $decrypted = Compat::crypto_box_open(
            $data,
            $this->nonce,
            Compat::crypto_box_keypair_from_secretkey_and_publickey($key->getPrivate(), $key->getPublic())
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Malformed message or invalid MAC');
        }

        return $decrypted;
    }

    /**
     * Method to encrypt a data string.
     *
     * @param   string  $data  The data string to encrypt.
     * @param   Key     $key   The key object to use for encryption.
     *
     * @return  string  The encrypted data string.
     *
     * @since   3.8.0
     * @throws  \RuntimeException
     */
    public function encrypt($data, Key $key)
    {
        // Validate key.
        if ($key->getType() !== 'sodium') {
            throw new \InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected sodium.');
        }

        if (!$this->nonce) {
            throw new \RuntimeException('Missing nonce to decrypt data');
        }

        return Compat::crypto_box(
            $data,
            $this->nonce,
            Compat::crypto_box_keypair_from_secretkey_and_publickey($key->getPrivate(), $key->getPublic())
        );
    }

    /**
     * Method to generate a new encryption key object.
     *
     * @param   array  $options  Key generation options.
     *
     * @return  Key
     *
     * @since   3.8.0
     * @throws  \RuntimeException
     */
    public function generateKey(array $options = [])
    {
        // Generate the encryption key.
        $pair = Compat::crypto_box_keypair();

        return new Key('sodium', Compat::crypto_box_secretkey($pair), Compat::crypto_box_publickey($pair));
    }

    /**
     * Check if the cipher is supported in this environment.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public static function isSupported(): bool
    {
        return class_exists(Compat::class);
    }

    /**
     * Set the nonce to use for encrypting/decrypting messages
     *
     * @param   string  $nonce  The message nonce
     *
     * @return  void
     *
     * @since   3.8.0
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
    }
}
