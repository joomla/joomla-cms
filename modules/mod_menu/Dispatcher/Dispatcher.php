<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Menu\Site\Dispatcher;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Dispatcher\ModuleDispatcher;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Menu\Site\Helper\MenuHelper;
use Joomla\Registry\Registry;

/**
 * Dispatcher class for mod_menu
 *
 * @since  4.0.0
 */
class Dispatcher extends ModuleDispatcher
{
	/**
	 * Dispatch the extension.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		$params = new Registry($this->module->params);

		$list       = MenuHelper::getList($params);
		$base       = MenuHelper::getBase($params);
		$active     = MenuHelper::getActive($params);
		$default    = MenuHelper::getDefault();
		$active_id  = $active->id;
		$default_id = $default->id;
		$path       = $base->tree;
		$showAll    = $params->get('showAllChildren');
		$class_sfx  = htmlspecialchars($params->get('class_sfx'), ENT_COMPAT, 'UTF-8');

		if (count($list))
		{
			require ModuleHelper::getLayoutPath('mod_menu', $params->get('layout', 'default'));
		}
	}
}
