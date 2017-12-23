<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latest
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Content\Administrator\Model\ArticlesModel;
use Joomla\Module\Latest\Administrator\Helper\ModLatestHelper;

$list = ModLatestHelper::getList($params, new ArticlesModel(array('ignore_request' => true)));

if ($params->get('automatic_title', 0))
{
	$module->title = ModLatestHelper::getTitle($params);
}

require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_latest', $params->get('layout', 'default'));
