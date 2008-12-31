<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 */
class UserViewUser extends JView
{
	public $document = null;
	protected $contact = null;

	/**
	 * Display the view
	 *
	 * @access	public
	 */
	function display($tpl = null)
	{
		$state = $this->get('State');
		$item = &$this->get('Item');

		$this->assignRef('state', $state);
		$this->assignRef('item', $item);

		if ($item->get('id')) {
			// Existing
		}
		else {
			// New
			$config		= &JComponentHelper::getParams('com_users');
			$acl		= &JFactory::getACL();
			$newGrp		= $config->get('new_usertype');
			$item->set('gid', $acl->get_group_id($newGrp, null, 'ARO'));
		}

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
		$isNew = ($this->item->get('id') == 0);
		JToolBarHelper::title(JText::_(($isNew ? 'Add User' : 'Edit User')), 'user');
		if (!$isNew) {
			JToolBarHelper::custom('user.save2copy', 'copy.png', 'copy_f2.png', 'Save To Copy', false);
		}
		JToolBarHelper::custom('user.save2new', 'new.png', 'new_f2.png', 'Save And New', false);
		JToolBarHelper::save('user.save');
		JToolBarHelper::apply('user.apply');
		JToolBarHelper::cancel('user.cancel');
		JToolBarHelper::help('screen.users.edit');
	}
}

