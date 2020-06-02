<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Popular\Administrator\Helper\PopularHelper;

$model = $app->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Administrator', ['ignore_request' => true]);
$list = PopularHelper::getList($params, $model);

// Get module data.
if ($params->get('automatic_title', 0))
{
	$module->title = PopularHelper::getTitle($params);
}

// Render the module
require ModuleHelper::getLayoutPath('mod_popular', $params->get('layout', 'default'));
