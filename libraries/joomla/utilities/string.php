<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Utilities
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JString has moved to jimport(\'joomla.string.string\'), please update your code.', JLog::WARNING, 'deprecated');

require_once JPATH_PLATFORM . '/joomla/string/string.php';
