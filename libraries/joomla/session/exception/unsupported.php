<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Exception class defining an unsupported session storage object
 *
 * @since       3.6.3
 * @deprecated  4.0  The CMS' Session classes will be replaced with the `joomla/session` package
 */
class JSessionExceptionUnsupported extends RuntimeException
{
}
