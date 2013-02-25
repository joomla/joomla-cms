<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML utility class for the installation application
 *
 * @package  Joomla.Installation
 * @since    1.6
 */
class JHtmlInstallation
{
	/**
	 * Method to generate the side bar
	 *
	 * @return  string  Markup for the side bar
	 *
	 * @since   1.6
	 */
	public static function stepbar()
	{

		// Determine if the configuration file path is writable.
		$path = JPATH_CONFIGURATION . '/configuration.php';
		$useftp = (file_exists($path)) ? !is_writable($path) : !is_writable(JPATH_CONFIGURATION . '/');

		$tabs = array();
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
			$html[] = self::getTab($tab, $tabs);
		}
		$html[] = '</ul>';
		return implode('', $html);
	}

	/**
	 * Method to generate the side bar
	 *
	 * @return  string  Markup for the side bar
	 *
	 * @since   3.0
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
			$html[] = self::getTab($tab, $tabs);
		}
		$html[] = '</ul>';

		return implode('', $html);
	}

	/**
	 * @param   string  $id
	 * @param   array   $tabs
	 *
	 * @return  string  Markup for the tab
	 *
	 * @since   3.0
	 */
	private static function getTab($id, $tabs)
	{
		$input = JFactory::getApplication()->input;
		$num   = self::getTabNumber($id, $tabs);
		$view  = self::getTabNumber($input->getWord('view'), $tabs);
		$tab   = '<span class="badge">' . $num . '</span> ' . JText::_('INSTL_STEP_' . strtoupper($id) . '_LABEL');

		if ($view + 1 == $num)
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

		return '<li class="step' . ($num == $view ? ' active' : '') . '" id="' . $id . '">' . $tab . '</li>';
	}

	/**
	 * @param   string  $id
	 * @param   array   $tabs
	 *
	 * @return  int
	 *
	 * @since   3.0
	 */
	private static function getTabNumber($id, $tabs)
	{
		$num = (int) array_search($id, $tabs);
		$num++;

		return $num;
	}
}
