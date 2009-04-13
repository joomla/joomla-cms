<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Registration
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Registration component
 *
 * @package		Joomla
 * @subpackage	Registration
 * @since 1.0
 */
class UserViewRegister extends JView
{
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$pathway  =& $mainframe->getPathway();
		$document =& JFactory::getDocument();
		$params	= &$mainframe->getParams();

	 	// Page Title
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	JText::_( 'Registration' ));
			}
		} else {
			$params->set('page_title',	JText::_( 'Registration' ));
		}
		$document->setTitle( $params->get( 'page_title' ) );

		$pathway->addItem( JText::_( 'New' ));

		// Load the form validation behavior
		JHtml::_('behavior.formvalidation');

		$user =& JFactory::getUser();
		$this->assignRef('user', $user);
		$this->assignRef('params',		$params);
		parent::display($tpl);
	}
}
