<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\View\Featured;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

/**
 * View class for a list of featured articles.
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
	 * @return  stdClass[]
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
		\Joomla\Component\Content\Administrator\Helper\ContentHelper::addSubmenu('featured');

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->transitions   = $this->get('Transitions');
		$this->vote          = PluginHelper::isEnabled('content', 'vote');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		$this->sidebar = \JHtmlSidebar::render();

		// We do not need to filter by language when multilingual is disabled
		if (!Multilanguage::isEnabled())
		{
			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
		}

		$transitions = [
			'publish' => [],
			'unpublish' => [],
			'archive' => [],
			'trash' => []
		];

		foreach ($this->transitions as $transition)
		{
			switch ($transition['stage_condition'])
			{
				case ContentComponent::CONDITION_PUBLISHED:
					$transitions['publish'][$transition['workflow_id']][$transition['from_stage_id']][] = $transition;
					break;

				case ContentComponent::CONDITION_UNPUBLISHED:
					$transitions['unpublish'][$transition['workflow_id']][$transition['from_stage_id']][] = $transition;
					break;

				case ContentComponent::CONDITION_ARCHIVED:
					$transitions['archive'][$transition['workflow_id']][$transition['from_stage_id']][] = $transition;
					break;

				case ContentComponent::CONDITION_TRASHED:
					$transitions['trash'][$transition['workflow_id']][$transition['from_stage_id']][] = $transition;
					break;
			}
		}

		Factory::getDocument()->addScriptOptions('articles.transitions', $transitions);

		$articles = [];

		foreach ($this->items as $item)
		{
			$articles['article-' . (int) $item->id] = Text::sprintf('COM_CONTENT_STAGE_ARTICLE_TITLE', $this->escape($item->title), (int) $item->id);
		}

		Factory::getDocument()->addScriptOptions('articles.items', $articles);

		Text::script('COM_CONTENT_ERROR_CANNOT_PUBLISH');
		Text::script('COM_CONTENT_ERROR_CANNOT_UNPUBLISH');
		Text::script('COM_CONTENT_ERROR_CANNOT_TRASH');
		Text::script('COM_CONTENT_ERROR_CANNOT_ARCHIVE');

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

		ToolbarHelper::title(Text::_('COM_CONTENT_FEATURED_TITLE'), 'star featured');

		if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0)
		{
			$toolbar->addNew('article.add');
		}

		if ($canDo->get('core.edit.state') || $canDo->get('core.execute.transition'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fa fa-globe')
				->buttonClass('btn btn-info')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();


			if ($canDo->get('core.execute.transition'))
			{
				$childBar->publish('articles.publish')->listCheck(true);

				$childBar->unpublish('articles.unpublish')->listCheck(true);
			}

			if ($canDo->get('core.edit.state'))
			{
				$childBar->standardButton('featured')
					->text('JFEATURE')
					->task('articles.featured')
					->listCheck(true);
				$childBar->standardButton('unfeatured')
					->text('JUNFEATURE')
					->task('articles.unfeatured')
					->listCheck(true);
			}

			if ($canDo->get('core.execute.transition'))
			{
				$childBar->archive('articles.archive')->listCheck(true);
			}

			if ($canDo->get('core.edit.state'))
			{
				$childBar->checkin('articles.checkin')->listCheck(true);
			}

			if ($canDo->get('core.execute.transition'))
			{
				$childBar->trash('articles.trash')->listCheck(true);
			}
		}

		if ($this->state->get('filter.condition') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
		{
			$toolbar->delete('articles.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($user->authorise('core.admin', 'com_content') || $user->authorise('core.options', 'com_content'))
		{
			$toolbar->preferences('com_content');
		}

		ToolbarHelper::help('JHELP_CONTENT_FEATURED_ARTICLES');
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
			'fp.ordering'    => Text::_('JGRID_HEADING_ORDERING'),
			'a.state'        => Text::_('JSTATUS'),
			'a.title'        => Text::_('JGLOBAL_TITLE'),
			'category_title' => Text::_('JCATEGORY'),
			'access_level'   => Text::_('JGRID_HEADING_ACCESS'),
			'a.created_by'   => Text::_('JAUTHOR'),
			'language'       => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.created'      => Text::_('JDATE'),
			'a.id'           => Text::_('JGRID_HEADING_ID'),
		);
	}
}
