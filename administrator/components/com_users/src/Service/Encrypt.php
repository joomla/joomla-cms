<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Service;

use Joomla\CMS\Encrypt\Aes;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Data encryption service.
 *
 * @since 4.2.0
 */
class Encrypt
{
    /**
     * The encryption engine used by this service
     *
     * @var    Aes
     * @since  4.2.0
     */
    private $aes;

    /**
     * EncryptService constructor.
     *
     * @since   4.2.0
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Encrypt the plaintext $data and return the ciphertext prefixed by ###AES128###
     *
     * @param   string  $data  The plaintext data
     *
     * @return  string  The ciphertext, prefixed by ###AES128###
     *
     * @since   4.2.0
     */
    public function encrypt(string $data): string
    {
        if (!is_object($this->aes)) {
            return $data;
        }

        $this->aes->setPassword($this->getPassword(), false);
        $encrypted = $this->aes->encryptString($data, true);

        return '###AES128###' . $encrypted;
    }

    /**
     * Decrypt the ciphertext, prefixed by ###AES128###, and return the plaintext.
     *
     * @param   string  $data    The ciphertext, prefixed by ###AES128###
     * @param   bool    $legacy  Use legacy key expansion. We recommend against using it.
     *
     * @return  string  The plaintext data
     *
     * @since   4.2.0
     */
    public function decrypt(string $data, bool $legacy = false): string
    {
        if (substr($data, 0, 12) != '###AES128###') {
            return $data;
        }

        $data = substr($data, 12);

        if (!is_object($this->aes)) {
            return $data;
        }

        $this->aes->setPassword($this->getPassword(), $legacy);
        $decrypted = $this->aes->decryptString($data, true);

        // Decrypted data is null byte padded. We have to remove the padding before proceeding.
        return rtrim($decrypted, "\0");
    }

    /**
     * Initialize the AES cryptography object
     *
     * @return  void
     * @since   4.2.0
     */
    private function initialize(): void
    {
        if (is_object($this->aes)) {
            return;
        }

        $password = $this->getPassword();

        if (empty($password)) {
            return;
        }

        $this->aes = new Aes('cbc');
        $this->aes->setPassword($password);
    }

    /**
     * Returns the password used to encrypt information in the component
     *
     * @return  string
     *
     * @since   4.2.0
     */
    private function getPassword(): string
    {
        try {
            return Factory::getApplication()->get('secret', '');
        } catch (\Exception $e) {
            return '';
        }
    }
}
