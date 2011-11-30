<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewUsers extends JView
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
		
		$this->prepareTable();

		$this->addToolbar();
		parent::display($tpl);
	}
	
	/**
	 * Prepare the table of the view
	 * 
	 * @since 2.5
	 */
	protected function prepareTable()
	{
		$canDo 		= UsersHelper::getActions();
		$listOrder	= $this->escape($this->state->get('list.ordering'));
		$listDirn	= $this->escape($this->state->get('list.direction'));
		$loggeduser = JFactory::getUser();
		
		jimport('joomla.html.grid');
		$table = new JGrid(array('class' => 'adminlist'));
		
		if(JRequest::getCmd('layout') == 'modal') {
			$field		= JRequest::getCmd('field');
			$function	= 'jSelectUser_'.$field;
			
			$table->addColumn('name')->addColumn('username')->addColumn('groups');
			$table->addRow(array(), 1)
				->setRowCell('name', JHtml::_('grid.sort', 'COM_USERS_HEADING_NAME', 'a.name', $listDirn, $listOrder), array('class' => 'left'))
				->setRowCell('username', JHtml::_('grid.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder), array('class' => 'nowrap', 'width' => '25%'))
				->setRowCell('groups', JHtml::_('grid.sort', 'COM_USERS_HEADING_GROUPS', 'group_names', $listDirn, $listOrder), array('class' => 'nowrap', 'width' => '25%'));
			$table->addRow(array(), 2)
				->setRowCell('name', $this->pagination->getListFooter(), array('colspan' => 3));
			
			$i = 0;
			foreach ($this->items as $i => $item) {
				$table->addRow(array('class' => 'row'.($i % 2)))
					->setRowCell('name', '<a class="pointer" onclick="if (window.parent) window.parent.'.$this->escape($function).'(\''.$item->id.'\', \''.$this->escape(addslashes($item->name)).'\');">
						'.$item->name.'</a>')
					->setRowCell('username', $item->username, array('align' => 'center'))
					->setRowCell('groups', nl2br($item->group_names), array('align' => 'left'));
			}

			$this->table = $table;
			return;
		}
		
		// Create columns
		$table->addColumn('checkbox')
			->addColumn('name')
			->addColumn('username')
			->addColumn('enabled')
			->addColumn('activated')
			->addColumn('usergroups')
			->addColumn('email')
			->addColumn('lastvisit')
			->addColumn('registerdate')
			->addColumn('id');
		
		// Create header row
		$table->addRow(array(), 1)
			->setRowCell('checkbox', '<input type="checkbox" name="checkall-toggle" value="" title="'.JText::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />', array('width' => '1%'))
			->setRowCell('name', JHtml::_('grid.sort', 'COM_USERS_HEADING_NAME', 'a.name', $listDirn, $listOrder), array('class' => 'left'))
			->setRowCell('username', JHtml::_('grid.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder), array('class' => 'nowrap', 'width' => '10%'))
			->setRowCell('enabled', JHtml::_('grid.sort', 'COM_USERS_HEADING_ENABLED', 'a.block', $listDirn, $listOrder), array('class' => 'nowrap', 'width' => '5%'))
			->setRowCell('activated', JHtml::_('grid.sort', 'COM_USERS_HEADING_ACTIVATED', 'a.activation', $listDirn, $listOrder), array('class' => 'nowrap', 'width' => '5%'))
			->setRowCell('usergroups', JText::_('COM_USERS_HEADING_GROUPS'), array('class' => 'nowrap', 'width' => '10%'))
			->setRowCell('email', JHtml::_('grid.sort', 'JGLOBAL_EMAIL', 'a.email', $listDirn, $listOrder), array('class' => 'nowrap', 'width' => '15%'))
			->setRowCell('lastvisit', JHtml::_('grid.sort', 'COM_USERS_HEADING_LAST_VISIT_DATE', 'a.lastvisitDate', $listDirn, $listOrder), array('class' => 'nowrap', 'width' => '10%'))
			->setRowCell('registerdate', JHtml::_('grid.sort', 'COM_USERS_HEADING_REGISTRATION_DATE', 'a.registerDate', $listDirn, $listOrder), array('class' => 'nowrap', 'width' => '10%'))
			->setRowCell('id', JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder), array('class' => 'nowrap', 'width' => '3%'))
		;

		// Create footer row
		$table->addRow(array(), 2)
			->setRowCell('checkbox', $this->pagination->getListFooter(), array('colspan' => '15'))
		;
		
		//Create body rows
		foreach ($this->items as $i => $item)
		{
			$canEdit	= $canDo->get('core.edit');
			$canChange	= $loggeduser->authorise('core.edit.state',	'com_users');
			// If this group is super admin and this user is not super admin, $canEdit is false
			if ((!$loggeduser->authorise('core.admin')) && JAccess::check($item->id, 'core.admin'))
			{
				$canEdit	= false;
				$canChange	= false;
			}
			$table->addRow(array('class' => 'row'.($i % 2)));
			if ($canEdit)
			{
				$table->setRowCell('checkbox', JHtml::_('grid.id', $i, $item->id), array('class' => 'center'));
			}
			else
			{
				$table->setRowCell('checkbox', '', array('class' => 'center'));
			}
			if ($canEdit)
			{
				$table->setRowCell('name', '<a href="'.JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->id).'" title="'.JText::sprintf('COM_USERS_EDIT_USER', $this->escape($item->name)).'">
						'.$this->escape($item->name).'</a>');
			}
			else
			{
				$table->setRowCell('name', $this->escape($item->name));
			}
			if (JDEBUG)
			{
				$table->setRowCell('name', '<div class="fltrt"><div class="button2-left smallsub"><div class="blank"><a href="'.JRoute::_('index.php?option=com_users&view=debuguser&user_id='.(int) $item->id).'">
						'.JText::_('COM_USERS_DEBUG_USER').'</a></div></div></div>', array(), false);
			}
			$table->setRowCell('username', $this->escape($item->username), array('class' => 'center'));
			if ($canChange)
			{
				if ($loggeduser->id != $item->id)
				{
					$table->setRowCell('enabled', JHtml::_('grid.boolean', $i, !$item->block, 'users.unblock', 'users.block'), array('class' => 'center'));
				}
				else
				{
					$table->setRowCell('enabled', JHtml::_('grid.boolean', $i, !$item->block, 'users.block', null), array('class' => 'center'));
				}
			}
			else
			{
				$table->setRowCell('enabled', JText::_($item->block ? 'JNO' : 'JYES'), array('class' => 'center'));
			}
			$table->setRowCell('activated', JHtml::_('grid.boolean', $i, !$item->activation, 'users.activate', null), array('class' => 'center'));
			if (substr_count($item->group_names,"\n") > 1)
			{
				$table->setRowCell('usergroups', '<span class="hasTip" title="'.JText::_('COM_USERS_HEADING_GROUPS').'::'.nl2br($item->group_names).'">'.JText::_('COM_USERS_USERS_MULTIPLE_GROUPS').'</span>', array('class' => 'center'));
			}
			else
			{
				$table->setRowCell('usergroups', nl2br($item->group_names), array('class' => 'center'));
			}
			$table->setRowCell('email', $this->escape($item->email), array('class' => 'center'));
			if ($item->lastvisitDate!='0000-00-00 00:00:00')
			{
				$table->setRowCell('lastvisit', JHtml::_('date',$item->lastvisitDate, 'Y-m-d H:i:s'), array('class' => 'center'));
			}
			else
			{
				$table->setRowCell('lastvisit', JText::_('JNEVER'), array('class' => 'center'));
			}
			$table->setRowCell('registerdate', JHtml::_('date',$item->registerDate, 'Y-m-d H:i:s'), array('class' => 'center'));
			$table->setRowCell('id', (int) $item->id, array('class' => 'center'));
			
		}
		
		$this->table = $table;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= UsersHelper::getActions();

		JToolBarHelper::title(JText::_('COM_USERS_VIEW_USERS_TITLE'), 'user');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('user.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('user.edit');
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::publish('users.activate', 'COM_USERS_TOOLBAR_ACTIVATE', true);
			JToolBarHelper::unpublish('users.block', 'COM_USERS_TOOLBAR_BLOCK', true);
			JToolBarHelper::custom('users.unblock', 'unblock.png', 'unblock_f2.png', 'COM_USERS_TOOLBAR_UNBLOCK', true);
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'users.delete');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_users');
			JToolBarHelper::divider();
		}

		JToolBarHelper::help('JHELP_USERS_USER_MANAGER');
	}
}
