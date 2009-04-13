<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Messages
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Messages component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Messages
 * @since 1.0
 */
class MessagesViewMessages extends JView
{
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$db					=& JFactory::getDBO();
		$user				=& JFactory::getUser();

		// Set toolbar items for the page
		JToolBarHelper::title(  JText::_( 'Private Messaging' ), 'inbox.png' );
		JToolBarHelper::deleteList();
		JToolBarHelper::addNewX();
		JToolBarHelper::help( 'screen.messages.inbox' );

		// Get data from the model
		$rows		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		$filter		= & $this->get( 'Filter');

		$this->assignRef('user',		$user);
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}