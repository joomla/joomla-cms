<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\View\Cpanel;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Registry\Registry;

/**
 * HTML View class for the Cpanel component
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Array of cpanel modules
	 *
	 * @var  array
	 */
	protected $modules = null;

	protected $cpanel;

	protected $menuitem;

	protected $position = 'cpanel';

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// Set toolbar items for the page
		ToolbarHelper::title(Text::_('COM_CPANEL'), 'home-2 cpanel');
		ToolbarHelper::help('screen.cpanel');

		$app = Factory::getApplication();
		$this->cpanel = $app->input->getCmd('extension');

		if ($this->cpanel)
		{
			$this->position .= '-' . $this->cpanel;
			$modules = ModuleHelper::getModules('menu');
			$module = false;

			foreach ($modules as $m)
			{
				if ($m->module == 'mod_menu')
				{
					$module = $m;
					break;
				}
			}

			if (!$module)
			{
				throw new \JViewGenericdataexception('NO_BACKEBD_MENU', 500);
			}

			$params = new Registry($module->params);
			$menutype      = $params->get('menutype', '*');

			if ($menutype === '*')
			{
				$name   = $params->get('preset', 'joomla');
				$root = MenusHelper::loadPreset($name);
			}
			else
			{
				$root = MenusHelper::getMenuItems($menutype, true);
			}

			$this->menuitem = $this->findMenuItem($root, $this->cpanel);
		}

		// Display the cpanel modules
		$this->modules = ModuleHelper::getModules($this->position);

		parent::display($tpl);
	}

	protected function findMenuItem(MenuItem $node, $alias)
	{
		$items = $node->getChildren();

		// Iterate through the whole level first before traversing deeper
		foreach ($items as $item)
		{
			if ($item->alias == $alias)
			{
				return $item;
			}
		}

		foreach ($items as $item)
		{
			$result = $this->findMenuItem($item, $alias);

			if ($result)
			{
				return $result;
			}
		}

		return false;
	}
}
