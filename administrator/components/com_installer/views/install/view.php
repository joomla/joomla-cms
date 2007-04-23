<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * Extension Manager Install View
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerViewInstall extends JView
{
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Extension Manager' ), 'install.png' );
		JToolBarHelper::help( 'screen.installer' );

		// Get data from the model
		$state = &$this->get('State');

		jimport('joomla.html.tooltips');

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_( 'Extension Manager' ));

		$paths = new stdClass();
		$paths->first = '';

		$this->assign('message', false);
		$this->assignRef('paths', $paths);
		$this->assignRef('state', $state);

		parent::display($tpl);
	}

	function results($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Extension Manager' ), 'install.png' );
		JToolBarHelper::help( 'screen.installer' );

		// Get data from the model
		$state = &$this->get('State');

		jimport('joomla.html.tooltips');

		$document = & JFactory::getDocument();
		$document->setTitle($state->get('message'));

		$install = new stdClass();
		$install->message = $state->get('message');
		$install->ext_message = $state->get('extension.message', '');

		if ($install->message) {
			$showMessage = true;
		} else {
			$showMessage = false;
		}

		$this->assign('message', $showMessage);
		$this->assignRef('install', $install);
		$this->assignRef('state', $state);

		parent::display($tpl);
	}
}
?>