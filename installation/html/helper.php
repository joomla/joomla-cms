<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML utility class for the installation application
 *
 * @since  1.6
 */
class InstallationHtmlHelper
{
	/**
	 * Method to generate the side bar.
	 *
	 * @return  string  Markup for the side bar.
	 *
	 * @since   1.6
	 */
	public static function stepbar()
	{
		// Determine if the configuration file path is writable.
		$path   = JPATH_CONFIGURATION . '/configuration.php';
		$useftp = file_exists($path) ? !is_writable($path) : !is_writable(JPATH_CONFIGURATION . '/');

		$tabs   = array();
		$tabs[] = 'site';
		$tabs[] = 'database';

		if ($useftp)
		{
			$tabs[] = 'ftp';
		}

		$tabs[] = 'summary';

		$html = array();
		$html[] = '<ul class="nav nav-tabs">';

		foreach ($tabs as $tab)
		{
			$html[] = static::getTab($tab, $tabs);
		}

		$html[] = '</ul>';

		return implode('', $html);
	}

	/**
	 * Method to generate the side bar.
	 *
	 * @return  string  Markup for the side bar.
	 *
	 * @since   3.1
	 */
	public static function stepbarlanguages()
	{
		$tabs = array();
		$tabs[] = 'languages';
		$tabs[] = 'defaultlanguage';
		$tabs[] = 'complete';

		$html = array();
		$html[] = '<ul class="nav nav-tabs">';

		foreach ($tabs as $tab)
		{
			$html[] = static::getTab($tab, $tabs);
		}

		$html[] = '</ul>';

		return implode('', $html);
	}

	/**
	 * Method to generate the navigation tab.
	 *
	 * @param   string  $id    The container ID.
	 * @param   array   $tabs  The navigation tabs.
	 *
	 * @return  string  Markup for the tab.
	 *
	 * @since   3.1
	 */
	private static function getTab($id, $tabs)
	{
		$input = JFactory::getApplication()->input;
		$num   = static::getTabNumber($id, $tabs);
		$view  = static::getTabNumber($input->getWord('view'), $tabs);
		$tab   = '<span class="badge">' . $num . '</span> ' . JText::_('INSTL_STEP_' . strtoupper($id) . '_LABEL');

		if ($view + 1 === $num)
		{
			$tab = '<a href="#" onclick="Install.submitform();">' . $tab . '</a>';
		}
		elseif ($view < $num)
		{
			$tab = '<span>' . $tab . '</span>';
		}
		else
		{
			$tab = '<a href="#" onclick="return Install.goToPage(\'' . $id . '\')">' . $tab . '</a>';
		}

		return '<li class="step' . ($num === $view ? ' active' : '') . '" id="' . $id . '">' . $tab . '</li>';
	}

	/**
	 * Method to determine the tab (step) number.
	 *
	 * @param   string  $id    The container ID.
	 * @param   array   $tabs  The navigation tabs.
	 *
	 * @return  integer  Tab number in navigation sequence.
	 *
	 * @since   3.1
	 */
	private static function getTabNumber($id, $tabs)
	{
		$num = (int) array_search($id, $tabs, true);
		$num++;

		return $num;
	}
}
