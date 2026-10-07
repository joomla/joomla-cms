<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Access\Exception;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Exception class defining an authentication failed event
 *
 * @since  4.0.0
 *
 * @deprecated  __DEPLOY_VERSION__ will be removed in 8.0
 *              Use the class with the Exception suffix instead.
 */
class AuthenticationFailed extends AuthenticationFailedException
{
}
