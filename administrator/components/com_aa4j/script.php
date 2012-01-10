<?php

/**
 * @version		$Id: script.php 18/09/2011 10.54
 * @package		Joomla!1.7
 * @subpackage	Components
 * @copyright	Copyright (C) 2005 - 2011 alikonweb, Inc. All rights reserved.
 * @author		alikon
 * @based on	http://joomlacode.org/gf/project/com_helloworld_1_6/
 * @license		License GNU General Public License version 2 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of aa4j component
 */
class com_aa4jInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{ 
			echo '<p>' . JText::_('COM_AA4J_INSTALL_TEXT') . '</p>';
		// $parent is the class calling this method
		$parent->getParent()->setRedirectURL('index.php?option=com_aa4j');
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_AA4J_UNINSTALL_TEXT') . '</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_AA4J_UPDATE_TEXT') . '</p>';
			$parent->getParent()->setRedirectURL('index.php?option=com_aa4j');
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_AA4J_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		$app = JFactory::getApplication();
		echo '<p>' . JText::_('COM_AA4J_POSTFLIGHT_' . $type . '_TEXT') .$app->getTemplate(). '</p>';
	}
}
