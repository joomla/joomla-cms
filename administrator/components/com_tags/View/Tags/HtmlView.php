<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Tags\Administrator\View\Tags;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Tags view class for the Tags package.
 *
 * @since  3.1
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
	 * @var  \Joomla\CMS\Pagination\Pagination
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
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

	/**
	 * Array used for displaying the levels filter
	 *
	 * @return  \stdClass[]
	 * @since  4.0.0
	 */
	protected $f_levels;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
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

		// Levels filter.
		$options   = array();
		$options[] = \JHtml::_('select.option', '1', \JText::_('J1'));
		$options[] = \JHtml::_('select.option', '2', \JText::_('J2'));
		$options[] = \JHtml::_('select.option', '3', \JText::_('J3'));
		$options[] = \JHtml::_('select.option', '4', \JText::_('J4'));
		$options[] = \JHtml::_('select.option', '5', \JText::_('J5'));
		$options[] = \JHtml::_('select.option', '6', \JText::_('J6'));
		$options[] = \JHtml::_('select.option', '7', \JText::_('J7'));
		$options[] = \JHtml::_('select.option', '8', \JText::_('J8'));
		$options[] = \JHtml::_('select.option', '9', \JText::_('J9'));
		$options[] = \JHtml::_('select.option', '10', \JText::_('J10'));

		$this->f_levels = $options;

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();

			// We do not need to filter by language when multilingual is disabled
			if (!\JLanguageMultilang::isEnabled())
			{
				unset($this->activeFilters['language']);
				$this->filterForm->removeField('language', 'filter');
			}
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since   3.1
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = ContentHelper::getActions('com_tags');
		$user  = \JFactory::getUser();

		// Get the toolbar object instance
		$bar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(\JText::_('COM_TAGS_MANAGER_TAGS'), 'tags');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('tag.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('tags.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('tags.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::archiveList('tags.archive');
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::checkin('tags.checkin');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_tags')
			&& $user->authorise('core.edit', 'com_tags')
			&& $user->authorise('core.edit.state', 'com_tags'))
		{
			$title = \JText::_('JTOOLBAR_BATCH');

			// Instantiate a new \JLayoutFile instance and render the batch button
			$layout = new FileLayout('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'tags.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('tags.trash');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_tags');
		}

		ToolbarHelper::help('JHELP_COMPONENTS_TAGS_MANAGER');
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
			'a.lft'      => \JText::_('JGRID_HEADING_ORDERING'),
			'a.state'    => \JText::_('JSTATUS'),
			'a.title'    => \JText::_('JGLOBAL_TITLE'),
			'a.access'   => \JText::_('JGRID_HEADING_ACCESS'),
			'a.language' => \JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id'       => \JText::_('JGRID_HEADING_ID')
		);
	}
}
