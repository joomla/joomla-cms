<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Object
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JObject has been moved to /object/object.php.', JLog::WARNING, 'deprecated');
require_once JPATH_PLATFORM . '/joomla/object/object.php';