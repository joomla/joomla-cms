<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Access\Exception;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Exception class defining a not allowed access
 *
 * @since  3.6.3
 *
 * @deprecated  __DEPLOY_VERSION__ will be removed in 8.0
 *              Use the class with the Exception suffix instead.
 */
class NotAllowed extends NotAllowedException
{
}
