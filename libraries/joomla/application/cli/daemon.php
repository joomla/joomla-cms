<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDaemon has been renamed to JApplicationDaemon.', JLog::WARNING, 'deprecated');

/**
 * Backward Compatability Stub for JApplicationDaemon
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
class JDaemon extends JApplicationDaemon
{

}