<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Component\Content\Administrator\Model\ArticlesModel;
use Joomla\Module\Popular\Administrator\Helper\PopularHelper;

$list = PopularHelper::getList($params, new ArticlesModel(array('ignore_request' => true)));

// Get module data.
if ($params->get('automatic_title', 0))
{
	$module->title = PopularHelper::getTitle($params);
}

// Render the module
require ModuleHelper::getLayoutPath('mod_popular', $params->get('layout', 'default'));
