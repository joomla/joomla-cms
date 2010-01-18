<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of clients.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersViewClients extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');
		$params		= JComponentHelper::getParams('com_banners');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('params',		$params);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'banners.php';

		$state	= $this->get('State');
		$canDo	= BannersHelper::getActions();

		JToolBarHelper::title(JText::_('Banners_Manager_Clients'), 'generic.png');
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('client.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('client.edit');
		}
		JToolBarHelper::divider();
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('clients.publish', 'publish.png', 'publish_f2.png', 'Publish', true);
			JToolBarHelper::custom('clients.unpublish', 'unpublish.png', 'unpublish_f2.png', 'Unpublish', true);
			JToolBarHelper::divider();
			if ($state->get('filter.published') != -1) {
				JToolBarHelper::archiveList('clients.archive');
			}
		}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'clients.delete');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('clients.trash');
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_banners');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.banners.clients');
	}
}
