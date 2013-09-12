<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_title
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Get the component title div
if (isset(JFactory::getApplication()->JComponentTitle))
{
	$title = JFactory::getApplication()->JComponentTitle;
}

require JModuleHelper::getLayoutPath('mod_title', $params->get('layout', 'default'));
