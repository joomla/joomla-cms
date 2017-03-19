<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\View;

defined('JPATH_PLATFORM') or die;

/**
 * Base class for a Joomla List View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  2.5.5
 */
class ListView extends HtmlView
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
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

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
	 * The flag which determine whether we want to show batch button
	 *
	 * @var bool
	 */
	protected $supportsBatch = true;

	/**
	 * The toolbar title
	 *
	 * @var string
	 */
	protected $toolbarTitle;

	/**
	 * The preview link
	 *
	 * @var string
	 */
	protected $previewLink;

	/**
	 * The help link for the view
	 *
	 * @var
	 */
	protected $helpLink;

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		// Set class properties from config data passed in constructor
		if (isset($config['canDo']))
		{
			$this->canDo = $config['canDo'];
		}

		if (isset($config['supports_batch']))
		{
			$this->supportsBatch = $config['supports_batch'];
		}

		if (isset($config['help_link']))
		{
			$this->helpLink = $config['help_link'];
		}
	}

	/**
	 * @param null $tpl
	 *
	 * @return mixed
	 */
	public function display($tpl = null)
	{
		// Prepare view data
		$this->initializeView();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// Build toolbar
		$this->addToolbar();

		// Render the view
		return parent::display($tpl);
	}

	/**
	 * Prepare view data
	 */
	protected function initializeView()
	{
		if ($this->getLayout() !== 'modal')
		{
			$helperClass = ucfirst(substr($this->option, 4));

			if (is_callable($helperClass . '::addSubmenu'))
			{
				call_user_func(array($helperClass, 'getActions'), $this->getName());
			}

			$this->sidebar = \JHtmlSidebar::render();
		}

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// canDo property should be built by the child class before, if not, generate default value
		if (!empty($this->canDo))
		{
			$this->canDo = new \JObject();
		}
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
		$canDo = $this->canDo;
		$user  = \JFactory::getUser();

		// Get the toolbar object instance
		$bar = \JToolbar::getInstance('toolbar');

		$viewName = $this->getName();
		$singularViewName = \Joomla\String\Inflector::getInstance()->toSingular($viewName);

		if (empty($this->toolbarTitle))
		{
			$this->toolbarTitle = \JText::_(strtoupper($this->option . '_' . $viewName . '_TITLE'));
		}

		\JToolbarHelper::title($this->toolbarTitle, 'stack ' . $singularViewName);

		if ($canDo->get('core.create'))
		{
			\JToolbarHelper::addNew($singularViewName . '.add');
		}

		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			\JToolbarHelper::editList($singularViewName . '.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			\JToolbarHelper::publish($viewName . '.publish', 'JTOOLBAR_PUBLISH', true);
			\JToolbarHelper::unpublish($viewName . '.unpublish', 'JTOOLBAR_UNPUBLISH', true);

			if (isset($this->items[0]->featured))
			{
				\JToolbarHelper::custom($viewName . '.featured', 'featured.png', 'featured_f2.png', 'JFEATURE', true);
				\JToolbarHelper::custom($viewName . '.unfeatured', 'unfeatured.png', 'featured_f2.png', 'JUNFEATURE', true);
			}

			\JToolbarHelper::archiveList('articles.archive');
			\JToolbarHelper::checkin('articles.checkin');
		}

		// Add a batch button
		if ($this->supportsBatch && $user->authorise('core.create', $this->option)
			&& $user->authorise('core.edit', $this->option)
			&& $user->authorise('core.edit.state', $this->option))
		{
			$title = \JText::_('JTOOLBAR_BATCH');

			// Instantiate a new \JLayoutFile instance and render the batch button
			$layout = new \JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			\JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', $viewName . '.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			\JToolbarHelper::trash($viewName . '.trash');
		}

		if ($user->authorise('core.admin', $this->option) || $user->authorise('core.options', $this->option))
		{
			\JToolbarHelper::preferences($this->option);
		}

		if ($this->helpLink)
		{
			\JToolbarHelper::help($this->helpLink);
		}
	}
}