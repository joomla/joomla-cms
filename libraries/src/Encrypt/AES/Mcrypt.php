<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Encrypt\AES;

use Joomla\CMS\Encrypt\Randval;

defined('JPATH_PLATFORM') or die;

class Mcrypt extends AbstractAES implements AesInterface
{
	protected $cipherType = MCRYPT_RIJNDAEL_128;

	protected $cipherMode = MCRYPT_MODE_CBC;

	public function setEncryptionMode($mode = 'cbc', $strength = 128)
	{
		switch ((int) $strength)
		{
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

		switch (strtolower($mode))
		{
			case 'ecb':
				$this->cipherMode = MCRYPT_MODE_ECB;
				break;

			default:
			case 'cbc':
				$this->cipherMode = MCRYPT_MODE_CBC;
				break;
		}

	}

	public function encrypt($plainText, $key, $iv = null)
	{
		$iv_size = $this->getBlockSize();
		$key     = $this->resizeKey($key, $iv_size);
		$iv      = $this->resizeKey($iv, $iv_size);

		if (empty($iv))
		{
			$randVal   = new Randval;
			$iv        = $randVal->generate($iv_size);
		}

		$cipherText = mcrypt_encrypt($this->cipherType, $key, $plainText, $this->cipherMode, $iv);
		$cipherText = $iv . $cipherText;

		return $cipherText;
	}

	public function decrypt($cipherText, $key)
	{
		$iv_size    = $this->getBlockSize();
		$key        = $this->resizeKey($key, $iv_size);
		$iv         = substr($cipherText, 0, $iv_size);
		$cipherText = substr($cipherText, $iv_size);
		$plainText  = mcrypt_decrypt($this->cipherType, $key, $cipherText, $this->cipherMode, $iv);

		return $plainText;
	}

	public function isSupported()
	{
		if (!function_exists('mcrypt_get_key_size'))
		{
			return false;
		}

		if (!function_exists('mcrypt_get_iv_size'))
		{
			return false;
		}

		if (!function_exists('mcrypt_create_iv'))
		{
			return false;
		}

		if (!function_exists('mcrypt_encrypt'))
		{
			return false;
		}

		if (!function_exists('mcrypt_decrypt'))
		{
			return false;
		}

		if (!function_exists('mcrypt_list_algorithms'))
		{
			return false;
		}

		if (!function_exists('hash'))
		{
			return false;
		}

		if (!function_exists('hash_algos'))
		{
			return false;
		}

		$algorightms = mcrypt_list_algorithms();

		if (!in_array('rijndael-128', $algorightms))
		{
			return false;
		}

		if (!in_array('rijndael-192', $algorightms))
		{
			return false;
		}

		if (!in_array('rijndael-256', $algorightms))
		{
			return false;
		}

		$algorightms = hash_algos();

		if (!in_array('sha256', $algorightms))
		{
			return false;
		}

		return true;
	}

	public function getBlockSize()
	{
		return mcrypt_get_iv_size($this->cipherType, $this->cipherMode);
	}
}