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
 * View class for a list of weblinks.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksViewWeblinks extends JView
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

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'weblinks.php';

		$state	= $this->get('State');
		$canDo	= WeblinksHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('Weblinks_Manager_Weblinks'), 'generic.png');
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('weblink.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('weblink.edit');
		}
		JToolBarHelper::divider();
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('weblinks.publish', 'publish.png', 'publish_f2.png', 'Publish', true);
			JToolBarHelper::custom('weblinks.unpublish', 'unpublish.png', 'unpublish_f2.png', 'Unpublish', true);
			if ($state->get('filter.published') != -1) {
				JToolBarHelper::archiveList('weblinks.archive');
			}
		}
		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'weblinks.delete');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('weblinks.trash');
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_weblinks');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.weblink');
	}
}
