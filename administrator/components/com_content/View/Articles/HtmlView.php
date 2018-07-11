<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\View\Articles;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Factory;

\JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/content.php');

/**
 * View class for a list of articles.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * List of authors. Each stdClass has two properties - value and text, containing the user id and user's name
	 * respectively
	 *
	 * @var  \stdClass
	 */
	protected $authors;

	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  \JPagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

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
	protected $sidebar;

	/**
	 * Array used for displaying the levels filter
	 *
	 * @return  \stdClass[]
	 * @since  4.0.0
	 */
	protected $f_levels;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			\ContentHelper::addSubmenu('articles');
		}

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->authors       = $this->get('Authors');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->vote          = PluginHelper::isEnabled('content', 'vote');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = \JHtmlSidebar::render();

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
			// We also need to change the category filter to show show categories with All or the forced language.
			if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
				$languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);

				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
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
		$canDo = ContentHelper::getActions('com_content', 'category', $this->state->get('filter.category_id'));
		$user  = Factory::getUser();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_CONTENT_ARTICLES_TITLE'), 'stack article');

		if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0)
		{
			$toolbar->addNew('article.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			$toolbar->publish('articles.publish')->listCheck(true);

			$toolbar->unpublish('articles.unpublish')->listCheck(true);

			$toolbar->standardButton('featured')
				->text('JFEATURE')
				->task('articles.featured')
				->listCheck(true);

			$toolbar->standardButton('unfeatured')
				->text('JUNFEATURE')
				->task('articles.unfeatured')
				->listCheck(true);

			$toolbar->archive('articles.archive')->listCheck(true);

			$toolbar->checkin('articles.checkin')->listCheck(true);
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_content')
			&& $user->authorise('core.edit', 'com_content')
			&& $user->authorise('core.edit.state', 'com_content'))
		{
			$toolbar->popupButton('batch')
				->text('JTOOLBAR_BATCH')
				->selector('collapseModal')
				->listCheck(true);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->delete('articles.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}
		elseif ($canDo->get('core.edit.state'))
		{
			$toolbar->trash('articles.trash')->listCheck(true);
		}

		if ($user->authorise('core.admin', 'com_content') || $user->authorise('core.options', 'com_content'))
		{
			$toolbar->preferences('com_content');
		}

		$toolbar->help('JHELP_CONTENT_ARTICLE_MANAGER');
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
			'a.ordering'     => Text::_('JGRID_HEADING_ORDERING'),
			'a.state'        => Text::_('JSTATUS'),
			'a.title'        => Text::_('JGLOBAL_TITLE'),
			'category_title' => Text::_('JCATEGORY'),
			'access_level'   => Text::_('JGRID_HEADING_ACCESS'),
			'a.created_by'   => Text::_('JAUTHOR'),
			'language'       => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.created'      => Text::_('JDATE'),
			'a.id'           => Text::_('JGRID_HEADING_ID'),
			'a.featured'     => Text::_('JFEATURED')
		);
	}
}
