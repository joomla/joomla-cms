<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Categories\Administrator\View\Categories;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;

/**
 * Categories view class for the Category package.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  Pagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Flag if an association exists
	 *
	 * @var  boolean
	 */
	protected $assoc;

	/**
	 * Form object for search filters
	 *
	 * @var  \JForm
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $string;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->assoc         = $this->get('Assoc');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = \JHtmlSidebar::render();

			// We do not need to filter by language when multilingual is disabled
			if (!\JLanguageMultilang::isEnabled())
			{
				unset($this->activeFilters['language']);
				$this->filterForm->removeField('language', 'filter');
			}
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			if ($forcedLanguage = \JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
				$languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);
			}
		}

		return parent::display($tpl);
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
		$categoryId = $this->state->get('filter.category_id');
		$component  = $this->state->get('filter.component');
		$section    = $this->state->get('filter.section');
		$canDo      = ContentHelper::getActions($component, 'category', $categoryId);
		$user       = \JFactory::getUser();

		// Get the toolbar object instance
		$bar = \JToolbar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($component == 'com_categories')
		{
			return;
		}

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = \JFactory::getLanguage();
		$lang->load($component, JPATH_BASE, null, false, true)
		|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component, null, false, true);

		// If a component categories title string is present, let's use it.
		if ($lang->hasKey($component_title_key = strtoupper($component . ($section ? "_$section" : '')) . '_CATEGORIES_TITLE'))
		{
			$title = \JText::_($component_title_key);
		}
		elseif ($lang->hasKey($component_section_key = strtoupper($component . ($section ? "_$section" : ''))))
		// Else if the component section string exits, let's use it
		{
			$title = \JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', $this->escape(\JText::_($component_section_key)));
		}
		else
		// Else use the base title
		{
			$title = \JText::_('COM_CATEGORIES_CATEGORIES_BASE_TITLE');
		}

		// Load specific css component
		\JHtml::_('stylesheet', $component . '/administrator/categories.css', array('version' => 'auto', 'relative' => true));

		// Prepare the toolbar.
		\JToolbarHelper::title($title, 'folder categories ' . substr($component, 4) . ($section ? "-$section" : '') . '-categories');

		if ($canDo->get('core.create') || count($user->getAuthorisedCategories($component, 'core.create')) > 0)
		{
			\JToolbarHelper::addNew('category.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			\JToolbarHelper::publish('categories.publish', 'JTOOLBAR_PUBLISH', true);
			\JToolbarHelper::unpublish('categories.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			\JToolbarHelper::archiveList('categories.archive');
		}

		if (\JFactory::getUser()->authorise('core.admin'))
		{
			\JToolbarHelper::checkin('categories.checkin');
		}

		// Add a batch button
		if ($canDo->get('core.create')
			&& $canDo->get('core.edit')
			&& $canDo->get('core.edit.state'))
		{
			$title = \JText::_('JTOOLBAR_BATCH');

			// Instantiate a new \JLayoutFile instance and render the batch button
			$layout = new FileLayout('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($canDo->get('core.admin'))
		{
			\JToolbarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete', $component))
		{
			\JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'categories.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			\JToolbarHelper::trash('categories.trash');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			\JToolbarHelper::preferences($component);
		}

		// Compute the ref_key if it does exist in the component
		if (!$lang->hasKey($ref_key = strtoupper($component . ($section ? "_$section" : '')) . '_CATEGORIES_HELP_KEY'))
		{
			$ref_key = 'JHELP_COMPONENTS_' . strtoupper(substr($component, 4) . ($section ? "_$section" : '')) . '_CATEGORIES';
		}

		/*
		 * Get help for the categories view for the component by
		 * -remotely searching in a language defined dedicated URL: *component*_HELP_URL
		 * -locally  searching in a component help file if helpURL param exists in the component and is set to ''
		 * -remotely searching in a component URL if helpURL param exists in the component and is NOT set to ''
		 */
		if ($lang->hasKey($lang_help_url = strtoupper($component) . '_HELP_URL'))
		{
			$debug = $lang->setDebug(false);
			$url = \JText::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = null;
		}

		\JToolbarHelper::help($ref_key, ComponentHelper::getParams($component)->exists('helpURL'), $url);
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
			'a.lft'       => \JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => \JText::_('JSTATUS'),
			'a.title'     => \JText::_('JGLOBAL_TITLE'),
			'a.access'    => \JText::_('JGRID_HEADING_ACCESS'),
			'language'    => \JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id'        => \JText::_('JGRID_HEADING_ID'),
		);
	}
}
