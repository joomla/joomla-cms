<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of newsfeeds.
 *
 * @since  1.6
 */
class NewsfeedsViewNewsfeeds extends JViewLegacy
{
	/**
	 * The list of newsfeeds
	 *
	 * @var    JObject
	 * @since  1.6
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    JObject
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Modal layout doesn't need the submenu.
		if ($this->getLayout() !== 'modal')
		{
			NewsfeedsHelper::addSubmenu('newsfeeds');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// We don't need toolbar in the modal layout.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			// We also need to change the category filter to show show categories with All or the forced language.
			if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into an hidden field.
				$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);

				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
			}
		}

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
		$state = $this->get('State');
		$canDo = JHelperContent::getActions('com_newsfeeds', 'category', $state->get('filter.category_id'));
		$user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolbar::getInstance('toolbar');
		JToolbarHelper::title(JText::_('COM_NEWSFEEDS_MANAGER_NEWSFEEDS'), 'feed newsfeeds');

		if (count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0)
		{
			JToolbarHelper::addNew('newsfeed.add');
		}

		if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('newsfeed.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('newsfeeds.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('newsfeeds.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('newsfeeds.archive');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::checkin('newsfeeds.checkin');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_newsfeeds')
			&& $user->authorise('core.edit', 'com_newsfeeds')
			&& $user->authorise('core.edit.state', 'com_newsfeeds'))
		{
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'newsfeeds.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('newsfeeds.trash');
		}

		if ($user->authorise('core.admin', 'com_newsfeeds') || $user->authorise('core.options', 'com_newsfeeds'))
		{
			JToolbarHelper::preferences('com_newsfeeds');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering'     => JText::_('JGRID_HEADING_ORDERING'),
			'a.published'    => JText::_('JSTATUS'),
			'a.name'         => JText::_('JGLOBAL_TITLE'),
			'category_title' => JText::_('JCATEGORY'),
			'a.access'       => JText::_('JGRID_HEADING_ACCESS'),
			'numarticles'    => JText::_('COM_NEWSFEEDS_NUM_ARTICLES_HEADING'),
			'a.cache_time'   => JText::_('COM_NEWSFEEDS_CACHE_TIME_HEADING'),
			'a.language'     => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id'           => JText::_('JGRID_HEADING_ID')
		);
	}
}
