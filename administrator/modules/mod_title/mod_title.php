<?php
/**
 * @version		$Id: mod_title.php 22338 2011-11-04 17:24:53Z github_bot $
 * @package		Joomla.Administrator
 * @subpackage	mod_title
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Get the component title div
$title = JFactory::getApplication()->get('JComponentTitle');

require JModuleHelper::getLayoutPath('mod_title', $params->get('layout', 'default'));
