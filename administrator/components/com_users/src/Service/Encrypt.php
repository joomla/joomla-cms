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

/**
 * Data encryption service.
 *
 * @since __DEPLOY_VERSION__
 */
class Encrypt
{
	/**
	 * The encryption engine used by this service
	 *
	 * @var    Aes
	 * @since  __DEPLOY_VERSION__
	 */
	private $aes;

	/**
	 * EncryptService constructor.
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function encrypt(string $data): string
	{
		if (!is_object($this->aes))
		{
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
	 * @param   bool    $legacy  Use legacy key expansion? Use it to decrypt data encrypted with FOF 3.
	 *
	 * @return  string  The plaintext data
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function decrypt(string $data, bool $legacy = false): string
	{
		if (substr($data, 0, 12) != '###AES128###')
		{
			return $data;
		}

		$data = substr($data, 12);

		if (!is_object($this->aes))
		{
			return $data;
		}

		$this->aes->setPassword($this->getPassword(), $legacy);
		$decrypted = $this->aes->decryptString($data, true, $legacy);

		// Decrypted data is null byte padded. We have to remove the padding before proceeding.
		return rtrim($decrypted, "\0");
	}

	/**
	 * Initialize the AES cryptography object
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	private function initialize(): void
	{
		if (is_object($this->aes))
		{
			return;
		}

		$password = $this->getPassword();

		if (empty($password))
		{
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
	 * @since   __DEPLOY_VERSION__
	 */
	private function getPassword(): string
	{
		try
		{
			return Factory::getApplication()->get('secret', '');
		}
		catch (\Exception $e)
		{
			return '';
		}
	}
}
