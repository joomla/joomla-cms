<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessViewLevel extends JView
{
	/**
	 * Display the view
	 *
	 * @access	public
	 */
	function display($tpl = null)
	{
		$state	= $this->get('State');
		$item	= $this->get('Item');

		if ($item->id) {
			// Existing
		}
		else {
			// New
		}
		$this->assignRef('state', $state);
		$this->assignRef('item', $item);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar
	 */
	private function _setToolbar()
	{
		$isNew = ($this->item->get('id') == 0);
		JToolBarHelper::title(JText::_(($isNew ? 'Access Control: Add Level' : 'Access Control: Edit Level')), 'user');
		if (!$isNew) {
			JToolBarHelper::custom('group.save2copy', 'copy.png', 'copy_f2.png', 'Toolbar Save To Copy', false);
		}
		JToolBarHelper::custom('group.save2new', 'new.png', 'new_f2.png', 'Toolbar Save And New', false);
		JToolBarHelper::save('group.save');
		JToolBarHelper::apply('group.apply');
		JToolBarHelper::cancel('group.cancel');
		JToolBarHelper::help('screen.groups.edit');
	}
}