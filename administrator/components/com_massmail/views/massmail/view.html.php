<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Massmail
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla.Administrator
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
			JHtml::_('select.option',  0, '- '. JText::_( 'All User Groups' ) .' -' )
		);
		$gtree = array_merge( $gtree, $acl->get_group_children_tree( null, 'users', false ) );

		$this->assignRef('gtree',		$gtree);

		parent::display( $tpl );
	}

}