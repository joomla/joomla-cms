<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\View\Cpanel;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

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

	/**
	 * Array of cpanel modules
	 *
	 * @var  array
	 */
	protected $quickicons = null;

	/**
	 * Moduleposition to load
	 *
	 * @var  string
	 */
	protected $position = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$extension = ApplicationHelper::stringURLSafe($app->input->getCmd('dashboard'));

		$title = Text::_('COM_CPANEL_DASHBOARD_BASE_TITLE');

		$position = ApplicationHelper::stringURLSafe($extension);

		// Generate a title for the view cPanel
		if (!empty($extension))
		{
			$parts = explode('.', $extension);

			$prefix = 'COM_CPANEL_DASHBOARD_';
			$lang = Factory::getLanguage();

			if (strpos($parts[0], 'com_') === false)
			{
				$prefix .= strtoupper($parts[0]);
			}
			else
			{
				$prefix = strtoupper($parts[0]) . '_DASHBOARD';

				// Need to load the language file
				$lang->load($parts[0], JPATH_BASE, null, false, true)
				|| $lang->load($parts[0], JPATH_ADMINISTRATOR . '/components/' . $parts[0], null, false, true);
				$lang->load($parts[0]);
			}

			$sectionkey = !empty($parts[1]) ?   '_' . strtoupper($parts[1]) : '';
			$key = $prefix . $sectionkey . '_TITLE';

			// Search for a component title
			if ($lang->hasKey($key))
			{
				$title = Text::_($key);
			}
		}

		// Set toolbar items for the page
		ToolbarHelper::title(Text::_($title, 'fa fa-home cpanel'));
		ToolbarHelper::help('screen.cpanel');

		// Display the cpanel modules
		$this->position = $position ? 'cpanel-' . $position : 'cpanel';
		$this->modules = ModuleHelper::getModules($this->position);

		$quickicons = $position ? 'icon-' . $position : 'icon';
		$this->quickicons = ModuleHelper::getModules($quickicons);

		parent::display($tpl);
	}
}
