<?php
/**
 * @version		$Id: view.html.php 21705 2011-06-28 21:19:50Z dextercowley $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
// Include the component HTML helpers.
//require_once(JPATH_COMPONENT.'/helpers');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'users.php');

/**
 * View class for a list of users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class Aa4jViewUsers extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
				
					
			JSubMenuHelper::addEntry(
			JText::_('COM_AA4J_CONTROL_PANEL'),
			'index.php?option=com_aa4j&view=application',
			'ausers'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_AA4J_VIEW_GEO_TITLE'),
			'index.php?option=com_aa4j&view=component&component=all',
			'ausers'
		);
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= UsersHelper::getActions();

		JToolBarHelper::title(JText::_('COM_AA4J_VIEW_USERS_TITLE'), 'user');
/*
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('user.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('user.edit');
		}
*/
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::custom('users.detect', 'refresh.png', 'refresh.png','COM_AA4J_TOOLBAR_DETECT', 'index.php?option=com_aa4j&task=detect', true);
			//$toolbar =& JToolBar::getInstance('toolbar');
			//$url = JRoute::_('index.php?option=com_aa4j&task=detect&format=raw');
			//$toolbar->prependButton( 'standard', 'refresh', 'Detect', 'detect', true );
			JToolBarHelper::unpublish('users.block', 'COM_AA4J_TOOLBAR_BLOCK', true);
			JToolBarHelper::custom('users.unblock', 'unblock.png', 'unblock_f2.png', 'COM_AA4J_TOOLBAR_UNBLOCK', true);
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'users.delete');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_aa4j');
			JToolBarHelper::divider();
		}

		JToolBarHelper::help('JHELP_USERS_USER_MANAGER');
	}
}
