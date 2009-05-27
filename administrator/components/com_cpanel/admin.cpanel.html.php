<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.html.pane');
jimport('joomla.application.module.helper');

/**
 * @package		Joomla.Administrator
 * @subpackage	Admin
 */
class HTML_cpanel
{
	/**
	* Control panel
	*/
	function display()
	{
		global $mainframe;

		$modules = &JModuleHelper::getModules('cpanel');
        // TODO: allowAllClose should default true in J!1.6, so remove the array when it does.
		$pane = &JPane::getInstance('sliders', array('allowAllClose' => true));
		echo $pane->startPane("content-pane");

		foreach ($modules as $module) {
			$title = $module->title ;
			echo $pane->startPanel($title, 'cpanel-panel-'.$module->name);
			echo JModuleHelper::renderModule($module);
			echo $pane->endPanel();
		}

		echo $pane->endPane();
	}
}