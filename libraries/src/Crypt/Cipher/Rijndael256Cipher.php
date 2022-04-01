<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt\Cipher;

defined('JPATH_PLATFORM') or die;

/**
 * Crypt cipher for Rijndael 256 encryption, decryption and key generation.
 *
 * @since       3.0.0
 * @deprecated  4.0   Without replacement use SodiumCipher
 */
class Rijndael256Cipher extends McryptCipher
{
	/**
	 * @var    integer  The mcrypt cipher constant.
	 * @link   https://www.php.net/manual/en/mcrypt.ciphers.php
	 * @since  3.0.0
	 */
	protected $type = MCRYPT_RIJNDAEL_256;

	/**
	 * @var    integer  The mcrypt block cipher mode.
	 * @link   https://www.php.net/manual/en/mcrypt.constants.php
	 * @since  3.0.0
	 */
	protected $mode = MCRYPT_MODE_CBC;

	/**
	 * @var    string  The JCrypt key type for validation.
	 * @since  3.0.0
	 */
	protected $keyType = 'rijndael256';
}
