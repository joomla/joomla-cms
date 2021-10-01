<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_title
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

// Get the component title div
if (isset($app->JComponentTitle))
{
	$title = $app->JComponentTitle;
}

require ModuleHelper::getLayoutPath('mod_title', $params->get('layout', 'default'));
