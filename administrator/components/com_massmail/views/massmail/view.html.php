<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Massmail
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Massmail
 * @since 1.0
 */
class MassmailViewMassmail extends JView
{
	function display($tpl = null)
	{
		$acl =& JFactory::getACL();

		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Mass Mail' ), 'massemail.png' );
		JToolBarHelper::custom('send', 'send.png', 'send_f2.png', 'Send Mail', false);
		JToolBarHelper::cancel( );
		JToolBarHelper::preferences('com_massmail', '200');
		JToolBarHelper::help( 'screen.massmail' );

		// get list of groups
		$gtree = array(
			JHTML::_('select.option',  0, '- '. JText::_( 'All User Groups' ) .' -' )
		);
		$gtree = array_merge( $gtree, $acl->get_group_children_tree( null, 'users', false ) );

		$this->assignRef('gtree',		$gtree);

		parent::display( $tpl );
	}

}