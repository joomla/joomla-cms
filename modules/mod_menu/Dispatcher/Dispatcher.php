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
use Joomla\Module\Menu\Site\Helper\MenuHelper;

/**
 * Dispatcher class for mod_menu
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher extends ModuleDispatcher
{
	/**
	 * Returns the layout data. This function can be overridden by subclasses to add more
	 * attributes for the layout.
	 *
	 * If false is returned, then it means that the dispatch process should be aborted.
	 *
	 * @return  array|false
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$data['list'] = MenuHelper::getList($data['params']);

		if (!count($data['list']))
		{
			return false;
		}

		$data['base']       = MenuHelper::getBase($data['params']);
		$data['active']     = MenuHelper::getActive($data['params']);
		$data['default']    = MenuHelper::getDefault();
		$data['active_id']  = $data['active'] ->id;
		$data['default_id'] = $data['default'] ->id;
		$data['path']       = $data['base']->tree;
		$data['showAll']    = $data['params']->get('showAllChildren');
		$data['class_sfx']  = htmlspecialchars($data['params']->get('class_sfx'), ENT_COMPAT, 'UTF-8');

		return $data;
	}
}
