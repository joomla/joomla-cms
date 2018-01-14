<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail\Exception;

defined('_JEXEC') or die;

/**
 * Exception indicating that PHP's mail function has been disabled
 *
 * @since  __DEPLOY_VERSION__
 */
class MailFunctionDisabledException extends \RuntimeException implements MailExceptionInterface
{
}
