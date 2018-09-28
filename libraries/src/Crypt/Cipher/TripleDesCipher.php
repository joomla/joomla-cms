<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt\Cipher;

defined('JPATH_PLATFORM') or die;

/**
 * JCrypt cipher for Triple DES encryption, decryption and key generation.
 *
 * @since       12.1
 * @deprecated  4.0   Without replacement use CryptoCipher
 */
class TripleDesCipher extends McryptCipher
{
	/**
	 * @var    integer  The mcrypt cipher constant.
	 * @link   https://secure.php.net/manual/en/mcrypt.ciphers.php
	 * @since  12.1
	 */
	protected $type = MCRYPT_3DES;

	/**
	 * @var    integer  The mcrypt block cipher mode.
	 * @link   https://secure.php.net/manual/en/mcrypt.constants.php
	 * @since  12.1
	 */
	protected $mode = MCRYPT_MODE_CBC;

	/**
	 * @var    string  The Crypt key type for validation.
	 * @since  12.1
	 */
	protected $keyType = '3des';
}
