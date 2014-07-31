<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Users access levels view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewLevels extends JViewLegacy
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

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= UsersHelper::getActions();

		JToolBarHelper::title(JText::_('COM_USERS_VIEW_LEVELS_TITLE'), 'levels');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('level.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('level.edit');
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'level.delete');
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_users');
			JToolBarHelper::divider();
		}
		JToolBarHelper::help('JHELP_USERS_ACCESS_LEVELS');
	}
}
