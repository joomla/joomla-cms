<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * @package		Users
 * @subpackage	com_users
 */
class UserViewUsers extends JView
{
	protected $document = null;
	protected $state = null;
	protected $items = null;
	protected $_total = null;
	protected $f_logged_in = null;
	protected $f_enabled = null;
	protected $f_activated = null;

	/**
	 * Display the view
	 *
	 * @access	public
	 */
	function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('List');
		$pagination	= $this->get('Pagination');

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		// Logged in filter
		$options	= array();
		$options[]	= JHtml::_('select.option', '0', 'Select Login Status');
		$options[]	= JHtml::_('select.option', '1', 'Logged In');
		$this->assign('f_logged_in', $options);

		// Enabled filter
		$options	= array();
		$options[]	= JHtml::_('select.option', '*', 'Select Enabled Status');
		$options[]	= JHtml::_('select.option', '1', 'No');
		$options[]	= JHtml::_('select.option', '0', 'Yes');
		$this->assign('f_enabled', $options);

		// Activated filter
		$options	= array();
		$options[]	= JHtml::_('select.option', '*', 'Select Activated Status');
		$options[]	= JHtml::_('select.option', '1', 'No');
		$options[]	= JHtml::_('select.option', '0', 'Yes');
		$this->assign('f_activated', $options);

		$this->_setToolBar();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar
	 *
	 * @access	private
	 */
	function _setToolBar()
	{
		JToolBarHelper::title(JText::_('user Manager'), 'user.png');
		JToolBarHelper::custom('user.logout', 'cancel.png', 'cancel_f2.png', 'Logout');
		JToolBarHelper::deleteList('', 'user.delete');
		JToolBarHelper::custom('user.edit', 'edit.png', 'edit_f2.png', 'Edit', true);
		JToolBarHelper::custom('user.edit', 'new.png', 'new_f2.png', 'New', false);
		JToolBarHelper::help('screen.users');
	}
}
