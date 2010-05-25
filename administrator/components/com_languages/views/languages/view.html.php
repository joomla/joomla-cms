<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
		JToolBarHelper::title(JText::_('COM_LANGUAGES_VIEW_LANGUAGES_TITLE'), 'langmanager.png');
		JToolBarHelper::addNew('language.add','JTOOLBAR_NEW');
		JToolBarHelper::editList('language.edit','JTOOLBAR_EDIT');
		JToolBarHelper::divider();
		JToolBarHelper::publishList('languages.publish','JTOOLBAR_PUBLISH');
		JToolBarHelper::unpublishList('languages.unpublish','JTOOLBAR_UNPUBLISH');
		if ($this->state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'languages.delete', 'JTOOLBAR_EMPTY_TRASH');
		} else {
			JToolBarHelper::trash('languages.trash','JTOOLBAR_TRASH');
		}
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_languages');
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_CONTENT');
	}
}
