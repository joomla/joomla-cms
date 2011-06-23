<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML Languages View class for the Languages component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class LanguagesViewLanguages extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	function display($tpl = null)
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
		require_once JPATH_COMPONENT.'/helpers/languages.php';
		$canDo	= LanguagesHelper::getActions();

		JToolBarHelper::title(JText::_('COM_LANGUAGES_VIEW_LANGUAGES_TITLE'), 'langmanager.png');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('language.add');
		}

		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('language.edit');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.edit.state')) {
			if ($this->state->get('filter.published') != 2) {
				JToolBarHelper::publishList('languages.publish');
				JToolBarHelper::unpublishList('languages.unpublish');
			}
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'languages.delete','JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('languages.trash');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_languages');
			JToolBarHelper::divider();
		}

		JToolBarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_CONTENT');
	}
}
