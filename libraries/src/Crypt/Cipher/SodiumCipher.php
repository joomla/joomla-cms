<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt\Cipher;

use Joomla\Crypt\Cipher\Sodium;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JCrypt cipher for sodium algorithm encryption, decryption and key generation.
 *
 * @since  3.8.0
 * @deprecated  __DEPLOY_VERSION__ will be removed in 8.0
 *               Please use \Joomla\Crypt\Cipher\Sodium instead
 */
class SodiumCipher extends Sodium
{
}
