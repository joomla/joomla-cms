<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Messages component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesViewMessages extends JView
{
	public $pagination;
	public $items;
	public $state;
	
	public function display($tpl = null)
	{
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$state = $this->get('State');

		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('state', $state);
		
		parent::display($tpl);
		$this->_setToolbar();
	}
	
	protected function _setToolbar()
	{
		JToolBarHelper::title(JText::_('Private Messaging'), 'inbox.png');
		JToolBarHelper::deleteList();
		JToolBarHelper::addNew();
		JToolBarHelper::custom('config', 'config.png', 'config_f2.png', 'Settings', false, false);
		JToolBarHelper::help('screen.messages.inbox');
	}
}