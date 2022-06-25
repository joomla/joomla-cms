<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Encrypt\AES;

use Joomla\CMS\Encrypt\Randval;

/**
 * Mcrypt implementation
 *
 * @since    4.0.0
 *
 * @deprecated 4.0.0 will be removed in 5.0.0
 */
class Mcrypt extends AbstractAES implements AesInterface
{
    /**
     * Cypher Type
     *
     * @var    string
     */
    protected $cipherType = MCRYPT_RIJNDAEL_128;

    /**
     * Cypher Mode
     *
     * @var    string
     */
    protected $cipherMode = MCRYPT_MODE_CBC;

    /**
     * Set the encryption mode
     *
     * @param   string   $mode      Encryption Mode
     * @param   integer  $strength  Encryption Strength
     *
     * @return   void
     */
    public function setEncryptionMode($mode = 'cbc', $strength = 128)
    {
        switch ((int) $strength) {
            default:
            case '128':
                $this->cipherType = MCRYPT_RIJNDAEL_128;
                break;

            case '192':
                $this->cipherType = MCRYPT_RIJNDAEL_192;
                break;

            case '256':
                $this->cipherType = MCRYPT_RIJNDAEL_256;
                break;
        }

        switch (strtolower($mode)) {
            case 'ecb':
                $this->cipherMode = MCRYPT_MODE_ECB;
                break;

            default:
            case 'cbc':
                $this->cipherMode = MCRYPT_MODE_CBC;
                break;
        }
    }

    /**
     * Encrypt the data
     *
     * @param   string  $plainText  Plaintext data
     * @param   string  $key        Encryption key
     * @param   string  $iv         IV for the encryption
     *
     * @return   string  Encrypted data
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

        $cipherText = mcrypt_encrypt($this->cipherType, $key, $plainText, $this->cipherMode, $iv);
        $cipherText = $iv . $cipherText;

        return $cipherText;
    }

    /**
     * Decrypt encrypted data
     *
     * @param   string  $cipherText  Encrypted data
     * @param   string  $key         Encryptionkey
     *
     * @return   string  Plaintext data
     */
    public function decrypt($cipherText, $key)
    {
        $iv_size    = $this->getBlockSize();
        $key        = $this->resizeKey($key, $iv_size);
        $iv         = substr($cipherText, 0, $iv_size);
        $cipherText = substr($cipherText, $iv_size);
        $plainText  = mcrypt_decrypt($this->cipherType, $key, $cipherText, $this->cipherMode, $iv);

        return $plainText;
    }

    /**
     * Is this adapter supported?
     *
     * @return  boolean
     */
    public function isSupported()
    {
        if (!\function_exists('mcrypt_get_key_size')) {
            return false;
        }

        if (!\function_exists('mcrypt_get_iv_size')) {
            return false;
        }

        if (!\function_exists('mcrypt_create_iv')) {
            return false;
        }

        if (!\function_exists('mcrypt_encrypt')) {
            return false;
        }

        if (!\function_exists('mcrypt_decrypt')) {
            return false;
        }

        if (!\function_exists('mcrypt_list_algorithms')) {
            return false;
        }

        if (!\function_exists('hash')) {
            return false;
        }

        if (!\function_exists('hash_algos')) {
            return false;
        }

        $algorigthms = mcrypt_list_algorithms();

        if (!\in_array('rijndael-128', $algorigthms)) {
            return false;
        }

        if (!\in_array('rijndael-192', $algorigthms)) {
            return false;
        }

        if (!\in_array('rijndael-256', $algorigthms)) {
            return false;
        }

        $algorigthms = hash_algos();

        if (!\in_array('sha256', $algorigthms)) {
            return false;
        }

        return true;
    }

    /**
     * Get the block size
     *
     * @return   integer
     */
    public function getBlockSize()
    {
        return mcrypt_get_iv_size($this->cipherType, $this->cipherMode);
    }
}
