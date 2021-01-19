<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

/**
 * Implements encrypted settings handling features
 *
 * @author nicholas
 */
class SecureSettings
{
	/**
	 * The filename for the settings encryption key
	 *
	 * @var   string
	 */
	protected $keyFilename = 'serverkey.php';

	/**
	 * Set the key filename e.g. 'serverkey.php';
	 *
	 * @param   string  $filename  The new filename to use
	 *
	 * @return  void
	 */
	public function setKeyFilename($filename)
	{
		$this->keyFilename = $filename;
	}

	/**
	 * Gets the configured server key, automatically loading the server key storage file
	 * if required.
	 *
	 * @return string
	 */
	public function getKey()
	{
		if (defined('AKEEBA_SERVERKEY'))
		{
			return base64_decode(AKEEBA_SERVERKEY);
		}

		$filename = dirname(__FILE__) . '/../' . $this->keyFilename;

		if (file_exists($filename))
		{
			include_once $filename;
		}

		if (defined('AKEEBA_SERVERKEY'))
		{
			return base64_decode(AKEEBA_SERVERKEY);
		}

		return '';
	}

	/**
	 * Do the server options allow us to use settings encryption?
	 *
	 * @return bool
	 */
	public function supportsEncryption()
	{
		// Do we have the encrypt.php plugin?
		if (!class_exists('\\Akeeba\\Engine\\Util\\Encrypt', true))
		{
			return false;
		}

		// Did the user intentionally disable settings encryption?
		$useEncryption = Platform::getInstance()->get_platform_configuration_option('useencryption', -1);

		if ($useEncryption == 0)
		{
			return false;
		}

		// Do we have base64_encode/_decode required for encryption?
		if (!function_exists('base64_encode') || !function_exists('base64_decode'))
		{
			return false;
		}

		// Pre-requisites met. We can encrypt and decrypt!
		return true;
	}

	/**
	 * Gets the preferred encryption mode. Currently, if mcrypt is installed and activated we will
	 * use AES128.
	 *
	 * @return string
	 */
	public function preferredEncryption()
	{
		$aes     = new Encrypt();
		$adapter = $aes->getAdapter();

		if (!$adapter->isSupported())
		{
			return 'CTR128';
		}

		return 'AES128';
	}

	/**
	 * Encrypts the settings using the automatically detected preferred algorithm
	 *
	 * @param   $rawSettings  string  The raw settings string
	 * @param   $key          string  The encryption key. Set to NULL to automatically find the key.
	 *
	 * @return  string  The encrypted data to store in the database
	 */
	public function encryptSettings($rawSettings, $key = null)
	{
		// Do we really support encryption?
		if (!$this->supportsEncryption())
		{
			return $rawSettings;
		}

		// Does any of the preferred encryption engines exist?
		$encryption = $this->preferredEncryption();

		if (empty($encryption))
		{
			return $rawSettings;
		}

		// Do we have a non-empty key to begin with?
		if (empty($key))
		{
			$key = $this->getKey();
		}

		if (empty($key))
		{
			return $rawSettings;
		}

		if ($encryption == 'AES128')
		{
			$encrypted = Factory::getEncryption()->AESEncryptCBC($rawSettings, $key);

			if (empty($encrypted))
			{
				$encryption = 'CTR128';
			}
			else
			{
				// Note: CBC returns the encrypted data as a binary string and requires Base 64 encoding
				$rawSettings = '###AES128###' . base64_encode($encrypted);
			}
		}

		if ($encryption == 'CTR128')
		{
			$encrypted = Factory::getEncryption()->AESEncryptCtr($rawSettings, $key, 128);

			if (!empty($encrypted))
			{
				// Note: CTR returns the encrypted data readily encoded in Base 64
				$rawSettings = '###CTR128###' . $encrypted;
			}
		}

		return $rawSettings;
	}

	/**
	 * Decrypts the encrypted settings and returns the plaintext INI string
	 *
	 * @param   string  $encrypted  The encrypted data
	 *
	 * @return  string  The decrypted data
	 */
	public function decryptSettings($encrypted, $key = null)
	{
		if (substr($encrypted, 0, 12) == '###AES128###')
		{
			$mode = 'AES128';
		}
		elseif (substr($encrypted, 0, 12) == '###CTR128###')
		{
			$mode = 'CTR128';
		}
		else
		{
			return $encrypted;
		}

		if (empty($key))
		{
			$key = $this->getKey();
		}

		if (empty($key))
		{
			return '';
		}

		$encrypted = substr($encrypted, 12);

		switch ($mode)
		{
			default:
			case 'AES128':
				$encrypted = base64_decode($encrypted);
				$decrypted = rtrim(Factory::getEncryption()->AESDecryptCBC($encrypted, $key), "\0");
				break;

			case 'CTR128':
				$decrypted = Factory::getEncryption()->AESDecryptCtr($encrypted, $key, 128);
				break;
		}

		if (empty($decrypted))
		{
			$decrypted = '';
		}

		return $decrypted;
	}
}
