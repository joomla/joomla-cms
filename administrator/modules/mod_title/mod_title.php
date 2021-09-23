<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_title
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Get the component title div
if (isset(JFactory::getApplication()->JComponentTitle))
{
	$title = JFactory::getApplication()->JComponentTitle;
}

require JModuleHelper::getLayoutPath('mod_title', $params->get('layout', 'default'));
