<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML Languages View class for the Languages component.
 *
 * @since  1.6
 */
class LanguagesViewLanguages extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		LanguagesHelper::addSubmenu('languages');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo = JHelperContent::getActions('com_languages');

		JToolbarHelper::title(JText::_('COM_LANGUAGES_VIEW_LANGUAGES_TITLE'), 'comments-2 langmanager');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('language.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('language.edit');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.edit.state'))
		{
			if ($this->state->get('filter.published') != 2)
			{
				JToolbarHelper::publishList('languages.publish');
				JToolbarHelper::unpublishList('languages.unpublish');
			}
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'languages.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('languages.trash');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin'))
		{
			// Add install languages link to the lang installer component.
			$bar = JToolbar::getInstance('toolbar');
			$bar->appendButton('Link', 'upload', 'COM_LANGUAGES_INSTALL', 'index.php?option=com_installer&view=languages');
			JToolbarHelper::divider();

			JToolbarHelper::preferences('com_languages');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_CONTENT');

		JHtmlSidebar::setAction('index.php?option=com_languages&view=languages');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);
	}

	/**
	 * Returns an array of fields the table can be sorted by.
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value.
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
				'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
				'a.published' => JText::_('JSTATUS'),
				'a.title' => JText::_('JGLOBAL_TITLE'),
				'a.title_native' => JText::_('COM_LANGUAGES_HEADING_TITLE_NATIVE'),
				'a.lang_code' => JText::_('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'),
				'a.sef' => JText::_('COM_LANGUAGES_FIELD_LANG_CODE_LABEL'),
				'a.image' => JText::_('COM_LANGUAGES_HEADING_LANG_IMAGE'),
				'a.access' => JText::_('JGRID_HEADING_ACCESS'),
				'a.lang_id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
