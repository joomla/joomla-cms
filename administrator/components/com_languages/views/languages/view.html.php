<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML Languages View class for the Languages component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 * @since       1.6
 */
class LanguagesViewLanguages extends JViewLegacy
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
		require_once JPATH_COMPONENT.'/helpers/languages.php';
		$canDo	= LanguagesHelper::getActions();

		JToolbarHelper::title(JText::_('COM_LANGUAGES_VIEW_LANGUAGES_TITLE'), 'langmanager.png');

		if ($canDo->get('core.create')) {
			JToolbarHelper::addNew('language.add');
		}

		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList('language.edit');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.edit.state')) {
			if ($this->state->get('filter.published') != 2) {
				JToolbarHelper::publishList('languages.publish');
				JToolbarHelper::unpublishList('languages.unpublish');
			}
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'languages.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		} elseif ($canDo->get('core.edit.state')) {
			JToolbarHelper::trash('languages.trash');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolbarHelper::preferences('com_languages');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_CONTENT');
	}
}
