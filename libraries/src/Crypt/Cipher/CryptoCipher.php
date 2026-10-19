<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt\Cipher;

use Joomla\Crypt\Cipher\Crypto;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Crypt cipher for encryption, decryption and key generation via the php-encryption library.
 *
 * @since       3.5
 *
 * @deprecated  4.3 will be removed in 7.0
 *              Will be removed without replacement use SodiumCipher instead
 */
class CryptoCipher extends Crypto
{
}
