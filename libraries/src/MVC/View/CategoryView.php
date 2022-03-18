<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

defined('JPATH_PLATFORM') or die;

/**
 * Base HTML View class for the a Category list
 *
 * @since  3.2
 */
class CategoryView extends HtmlView
{
	/**
	 * State data
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  3.2
	 */
	protected $state;

	/**
	 * Category items data
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $items;

	/**
	 * The category model object for this category
	 *
	 * @var    \JModelCategory
	 * @since  3.2
	 */
	protected $category;

	/**
	 * The list of other categories for this extension.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $categories;

	/**
	 * Pagination object
	 *
	 * @var    \JPagination
	 * @since  3.2
	 */
	protected $pagination;

	/**
	 * Child objects
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $children;

	/**
	 * The name of the extension for the category
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $extension;

	/**
	 * The name of the view to link individual items to
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $viewName;

	/**
	 * Default title to use for page title
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $defaultPageTitle;

	/**
	 * Whether to run the standard Joomla plugin events.
	 * Off by default for b/c
	 *
	 * @var    bool
	 * @since  3.5
	 */
	protected $runPlugins = false;

	/**
	 * Method with common display elements used in category list displays
	 *
	 * @return  boolean|\JException|void  Boolean false or \JException instance on error, nothing otherwise
	 *
	 * @since   3.2
	 */
	public function commonCategoryDisplay()
	{
		$app    = \JFactory::getApplication();
		$user   = \JFactory::getUser();
		$params = $app->getParams();

		// Get some data from the models
		$model       = $this->getModel();
		$paramsModel = $model->getState('params');

		$paramsModel->set('check_access_rights', 0);
		$model->setState('params', $paramsModel);

		$state       = $this->get('State');
		$category    = $this->get('Category');
		$children    = $this->get('Children');
		$parent      = $this->get('Parent');

		if ($category == false)
		{
			return \JError::raiseError(404, \JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		if ($parent == false)
		{
			return \JError::raiseError(404, \JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		// Check whether category access level allows access.
		$groups = $user->getAuthorisedViewLevels();

		if (!in_array($category->access, $groups))
		{
			return \JError::raiseError(403, \JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			\JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Setup the category parameters.
		$cparams          = $category->getParams();
		$category->params = clone $params;
		$category->params->merge($cparams);

		$children = array($category->id => $children);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));

		if ($this->runPlugins)
		{
			\JPluginHelper::importPlugin('content');

			foreach ($items as $itemElement)
			{
				$itemElement = (object) $itemElement;
				$itemElement->event = new \stdClass;

				// For some plugins.
				!empty($itemElement->description) ? $itemElement->text = $itemElement->description : $itemElement->text = null;

				$dispatcher = \JEventDispatcher::getInstance();

				$dispatcher->trigger('onContentPrepare', array($this->extension . '.category', &$itemElement, &$itemElement->params, 0));

				$results = $dispatcher->trigger('onContentAfterTitle', array($this->extension . '.category', &$itemElement, &$itemElement->core_params, 0));
				$itemElement->event->afterDisplayTitle = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onContentBeforeDisplay', array($this->extension . '.category', &$itemElement, &$itemElement->core_params, 0));
				$itemElement->event->beforeDisplayContent = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onContentAfterDisplay', array($this->extension . '.category', &$itemElement, &$itemElement->core_params, 0));
				$itemElement->event->afterDisplayContent = trim(implode("\n", $results));

				if ($itemElement->text)
				{
					$itemElement->description = $itemElement->text;
				}
			}
		}

		$maxLevel         = $params->get('maxLevel', -1) < 0 ? PHP_INT_MAX : $params->get('maxLevel', PHP_INT_MAX);
		$this->maxLevel   = &$maxLevel;
		$this->state      = &$state;
		$this->items      = &$items;
		$this->category   = &$category;
		$this->children   = &$children;
		$this->params     = &$params;
		$this->parent     = &$parent;
		$this->pagination = &$pagination;
		$this->user       = &$user;

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active = $app->getMenu()->getActive();

		if ($active
			&& $active->component == $this->extension
			&& isset($active->query['view'], $active->query['id'])
			&& $active->query['view'] == 'category'
			&& $active->query['id'] == $this->category->id)
		{
			if (isset($active->query['layout']))
			{
				$this->setLayout($active->query['layout']);
			}
		}
		elseif ($layout = $category->params->get('category_layout'))
		{
			$this->setLayout($layout);
		}

		$this->category->tags = new \JHelperTags;
		$this->category->tags->getItemTags($this->extension . '.category', $this->category->id);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$this->prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Method to prepares the document
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function prepareDocument()
	{
		$app           = \JFactory::getApplication();
		$menus         = $app->getMenu();
		$this->pathway = $app->getPathway();
		$title         = null;

		// Because the application sets a default page title, we need to get it from the menu item itself
		$this->menu = $menus->getActive();

		if ($this->menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $this->menu->title));
		}
		else
		{
			$this->params->def('page_heading', \JText::_($this->defaultPageTitle));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = \JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = \JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

	/**
	 * Method to add an alternative feed link to a category layout.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function addFeed()
	{
		if ($this->params->get('show_feed_link', 1) == 1)
		{
			$link    = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(\JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(\JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
