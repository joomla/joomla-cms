<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Exception;

defined('JPATH_PLATFORM') or die;

/**
 * Exception class defining an error connecting to the cache storage engine
 *
 * @since  3.6.3
 */
class CacheConnectingException extends \RuntimeException implements CacheExceptionInterface
{
}
