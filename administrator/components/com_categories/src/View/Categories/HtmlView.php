<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\View\Categories;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

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
	 * Is this view an Empty State
	 *
	 * @var  boolean
	 * @since 4.0.0
	 */
	private $isEmptyState = false;

	/**
	 * Display the view
	 *
	 * @param   string|null  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @throws  GenericDataException
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->assoc         = $this->get('Assoc');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Written this way because we only want to call IsEmptyState if no items, to prevent always calling it when not needed.
		if (!count($this->items) && $this->isEmptyState = $this->get('IsEmptyState'))
		{
			$this->setLayout('emptystate');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
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

			// We do not need to filter by language when multilingual is disabled
			if (!Multilanguage::isEnabled())
			{
				unset($this->activeFilters['language']);
				$this->filterForm->removeField('language', 'filter');
			}
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
				$languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);
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
		$categoryId = $this->state->get('filter.category_id');
		$component  = $this->state->get('filter.component');
		$section    = $this->state->get('filter.section');
		$canDo      = ContentHelper::getActions($component, 'category', $categoryId);
		$user       = Factory::getApplication()->getIdentity();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($component == 'com_categories')
		{
			return;
		}

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = Factory::getLanguage();
		$lang->load($component, JPATH_BASE)
		|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);

		// If a component categories title string is present, let's use it.
		if ($lang->hasKey($component_title_key = strtoupper($component . ($section ? "_$section" : '')) . '_CATEGORIES_TITLE'))
		{
			$title = Text::_($component_title_key);
		}
		elseif ($lang->hasKey($component_section_key = strtoupper($component . ($section ? "_$section" : ''))))
		// Else if the component section string exists, let's use it.
		{
			$title = Text::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', $this->escape(Text::_($component_section_key)));
		}
		else
		// Else use the base title
		{
			$title = Text::_('COM_CATEGORIES_CATEGORIES_BASE_TITLE');
		}

		// Load specific css component
		/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = $this->document->getWebAssetManager();
		$wa->getRegistry()->addExtensionRegistryFile($component);

		if ($wa->assetExists('style', $component . '.admin-categories'))
		{
			$wa->useStyle($component . '.admin-categories');
		}
		else
		{
			$wa->registerAndUseStyle($component . '.admin-categories', $component . '/administrator/categories.css');
		}

		// Prepare the toolbar.
		ToolbarHelper::title($title, 'folder categories ' . substr($component, 4) . ($section ? "-$section" : '') . '-categories');

		if ($canDo->get('core.create') || count($user->getAuthorisedCategories($component, 'core.create')) > 0)
		{
			$toolbar->addNew('category.add');
		}

		if (!$this->isEmptyState && ($canDo->get('core.edit.state') || $user->authorise('core.admin')))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canDo->get('core.edit.state'))
			{
				$childBar->publish('categories.publish')->listCheck(true);

				$childBar->unpublish('categories.unpublish')->listCheck(true);

				$childBar->archive('categories.archive')->listCheck(true);
			}

			if ($user->authorise('core.admin'))
			{
				$childBar->checkin('categories.checkin')->listCheck(true);
			}

			if ($canDo->get('core.edit.state') && $this->state->get('filter.published') != -2)
			{
				$childBar->trash('categories.trash')->listCheck(true);
			}

			// Add a batch button
			if ($canDo->get('core.create')
				&& $canDo->get('core.edit')
				&& $canDo->get('core.edit.state'))
			{
				$childBar->popupButton('batch')
					->text('JTOOLBAR_BATCH')
					->selector('collapseModal')
					->listCheck(true);
			}
		}

		if (!$this->isEmptyState && $canDo->get('core.admin'))
		{
			$toolbar->standardButton('refresh')
				->text('JTOOLBAR_REBUILD')
				->task('categories.rebuild');
		}

		if (!$this->isEmptyState && $this->state->get('filter.published') == -2 && $canDo->get('core.delete', $component))
		{
			$toolbar->delete('categories.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences($component);
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
			$url = Text::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = null;
		}

		$toolbar->help($ref_key, ComponentHelper::getParams($component)->exists('helpURL'), $url);
	}
}
