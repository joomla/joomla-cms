<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application\Exception;

use Throwable;

\defined('JPATH_PLATFORM') or die;

/**
 * Exception class for a website that is in offline mode!
 *
 * @since  __DEPLOY_VERSION__
 */
class OfflineWebsiteException extends \RuntimeException
{
}
