<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessViewRules extends JView
{
	public $items;

	public $pagination;

	/**
	 * Display the view
	 *
	 * @access	public
	 */
	function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination', $pagination);

		$this->_setToolBar($state->get('list.acl_type',1));
		parent::display($tpl);
	}

	/**
	 * Display the toolbar
	 */
	private function _setToolBar($type)
	{
		JToolBarHelper::title(JText::_('Access Control: Rules Type '.(int) $type));
		JToolBarHelper::custom('acl.edit', 'edit.png', 'edit_f2.png', 'Edit', true);
		JToolBarHelper::custom('acl.edit', 'new.png', 'new_f2.png', 'ACL New Rule', false);
		JToolBarHelper::deleteList('','acl.delete');
	}
}