<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View for language overrides list.
 *
 * @since  2.5
 */
class LanguagesViewOverrides extends JViewLegacy
{
	/**
	 * The items to list.
	 *
	 * @var		array
	 * @since	2.5
	 */
	protected $items;

	/**
	 * The pagination object.
	 *
	 * @var		object
	 * @since	2.5
	 */
	protected $pagination;

	/**
	 * The model state.
	 *
	 * @var		object
	 * @since	2.5
	 */
	protected $state;

	/**
	 * The sidebar markup
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $sidebar;

	/**
	 * An array containing all frontend and backend languages
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $languages;

	/**
	 * Displays the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Overrides');
		$this->languages  = $this->get('Languages');
		$this->pagination = $this->get('Pagination');

		LanguagesHelper::addSubmenu('overrides');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new JViewGenericdataexception(implode("\n", $errors));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function addToolbar()
	{
		// Get the results for each action
		$canDo = JHelperContent::getActions('com_languages');

		JToolbarHelper::title(JText::_('COM_LANGUAGES_VIEW_OVERRIDES_TITLE'), 'comments-2 langmanager');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('override.add');
		}

		if ($canDo->get('core.edit') && $this->pagination->total)
		{
			JToolbarHelper::editList('override.edit');
		}

		if ($canDo->get('core.delete') && $this->pagination->total)
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'overrides.delete', 'JTOOLBAR_DELETE');
		}

		if (JFactory::getUser()->authorise('core.admin'))
		{
			JToolbarHelper::custom('overrides.purge', 'refresh.png', 'refresh_f2.png', 'COM_LANGUAGES_VIEW_OVERRIDES_PURGE', false);
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_languages');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_OVERRIDES');

		JHtmlSidebar::setAction('index.php?option=com_languages&view=overrides');

		JHtmlSidebar::addFilter(
			// @todo need a label here
			'',
			'filter_language_client',
			JHtml::_('select.options', $this->languages, null, 'text', $this->state->get('filter.language_client')),
			true
		);

		$this->sidebar = JHtmlSidebar::render();
	}
}
