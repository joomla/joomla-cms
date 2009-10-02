<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksViewWeblinks extends JView
{
	public $state;
	public $items;
	public $pagination;

	/**
	 * Display the view
	 *
	 * @return	void
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',			$state);
		$this->assignRef('items',			$items);
		$this->assignRef('pagination',		$pagination);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		$user	= JFactory::getUser();

		JToolBarHelper::title(JText::_('Weblinks_Manager_Weblinks'), 'generic.png');
		if ($user->authorise('core.create', 'com_weblinks')) {
			JToolBarHelper::addNew('weblink.add');
		}
		if ($user->authorise('core.edit', 'com_weblinks')) {
			JToolBarHelper::editList('weblink.edit');
		}
			JToolBarHelper::divider();
		if ($user->authorise('core.edit.state', 'com_weblinks'))
		{
			JToolBarHelper::publishList('weblinks.publish');
			JToolBarHelper::unpublishList('weblinks.unpublish');
		}
		if ($this->state->get('filter.state') == -2 && $user->authorise('core.delete', 'com_weblinks')) {
			JToolBarHelper::deleteList('', 'weblinks.delete', 'JToolbar_Empty_trash');
		}
		else if ($user->authorise('core.edit.state', 'com_weblinks')){
			JToolBarHelper::trash('weblinks.trash');
		}
		if ($user->authorise('core.admin', 'com_weblinks'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_weblinks');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.weblink');
	}
}
