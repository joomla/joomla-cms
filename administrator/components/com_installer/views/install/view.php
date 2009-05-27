<?php
/**
 * @version		$Id: view.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Extension Manager Install View
 *
 * @package		Joomla.Administrator
 * @subpackage	Installer
 * @since		1.5
 */

include_once(dirname(__FILE__).DS.'..'.DS.'default'.DS.'view.php');

class InstallerViewInstall extends InstallerViewDefault
{
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::help('screen.installer');

		$paths = new stdClass();
		$paths->first = '';

		$this->assignRef('paths', $paths);
		$this->assignRef('state', $this->get('state'));

		parent::display($tpl);
	}

}