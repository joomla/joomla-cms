<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Search HTML view class for the Finder package.
 *
 * @since  2.5
 */
class FinderViewSearch extends JViewLegacy
{
	/**
	 * The query indexer object
	 *
	 * @var    FinderIndexerQuery
	 * @since  __DEPLOY_VERSION__
	 */
	protected $query;

	/**
	 * The page parameters
	 *
	 * @var  \Joomla\Registry\Registry|null
	 */
	protected $params = null;

	/**
	 * The model state
	 *
	 * @var    JObject
	 */
	protected $state;

	/**
	 * The logged in user
	 *
	 * @var    JUser|null
	 */
	protected $user = null;

	/**
	 * The results of the search
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $results = array();

	/**
	 * The total number of results for the search query
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $total = 0;

	/**
	 * The pagination object
	 *
	 * @var    JPagination|null
	 * @since  __DEPLOY_VERSION__
	 */
	protected $pagination = null;

	/**
	 * The suggested search query
	 *
	 * @var   string|false
	 * @since __DEPLOY_VERSION__
	 */
	protected $suggested = false;

	/**
	 * The explained (human-readable) search query
	 *
	 * @var   string|null
	 * @since __DEPLOY_VERSION__
	 */
	protected $explained = null;

	/**
	 * The page class suffix
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $pageclass_sfx = '';

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  JError object on failure, void on success.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();

		// Get view data.
		$state = $this->get('State');
		$query = $this->get('Query');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderQuery') : null;
		$results = $this->get('Results');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderResults') : null;
		$total = $this->get('Total');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderTotal') : null;
		$pagination = $this->get('Pagination');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderPagination') : null;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// Configure the pathway.
		if (!empty($query->input))
		{
			$app->getPathway()->addItem($this->escape($query->input));
		}

		// Push out the view data.
		$this->state = &$state;
		$this->params = &$params;
		$this->query = &$query;
		$this->results = &$results;
		$this->total = &$total;
		$this->pagination = &$pagination;

		// Check for a double quote in the query string.
		if (strpos($this->query->input, '"'))
		{
			// Get the application router.
			$router = &$app::getRouter();

			// Fix the q variable in the URL.
			if ($router->getVar('q') !== $this->query->input)
			{
				$router->setVar('q', $this->query->input);
			}
		}

		// Log the search
		JSearchHelper::logSearch($this->query->input, 'com_finder');

		// Push out the query data.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		$this->suggested = JHtml::_('query.suggested', $query);
		$this->explained = JHtml::_('query.explained', $query);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active = $app->getMenu()->getActive();
		if (isset($active->query['layout']))
		{
			// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		$this->prepareDocument($query);

		JDEBUG ? JProfiler::getInstance('Application')->mark('beforeFinderLayout') : null;

		parent::display($tpl);

		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderLayout') : null;
	}

	/**
	 * Method to get hidden input fields for a get form so that control variables
	 * are not lost upon form submission
	 *
	 * @return  string  A string of hidden input form fields
	 *
	 * @since   2.5
	 */
	protected function getFields()
	{
		$fields = null;

		// Get the URI.
		$uri = JUri::getInstance(JRoute::_($this->query->toUri()));
		$uri->delVar('q');
		$uri->delVar('o');
		$uri->delVar('t');
		$uri->delVar('d1');
		$uri->delVar('d2');
		$uri->delVar('w1');
		$uri->delVar('w2');
		$elements = $uri->getQuery(true);

		// Create hidden input elements for each part of the URI.
		foreach ($elements as $n => $v)
		{
			if (is_scalar($v))
			{
				$fields .= '<input type="hidden" name="' . $n . '" value="' . $v . '" />';
			}
		}

		return $fields;
	}

	/**
	 * Method to get the layout file for a search result object.
	 *
	 * @param   string  $layout  The layout file to check. [optional]
	 *
	 * @return  string  The layout file to use.
	 *
	 * @since   2.5
	 */
	protected function getLayoutFile($layout = null)
	{
		// Create and sanitize the file name.
		$file = $this->_layout . '_' . preg_replace('/[^A-Z0-9_\.-]/i', '', $layout);

		// Check if the file exists.
		jimport('joomla.filesystem.path');
		$filetofind = $this->_createFileName('template', array('name' => $file));
		$exists = JPath::find($this->_path['template'], $filetofind);

		return ($exists ? $layout : 'result');
	}

	/**
	 * Prepares the document
	 *
	 * @param   FinderIndexerQuery  $query  The search query
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function prepareDocument($query)
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_FINDER_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($layout = $this->params->get('article_layout'))
		{
			$this->setLayout($layout);
		}

		// Configure the document meta-description.
		if (!empty($this->explained))
		{
			$explained = $this->escape(html_entity_decode(strip_tags($this->explained), ENT_QUOTES, 'UTF-8'));
			$this->document->setDescription($explained);
		}

		// Configure the document meta-keywords.
		if (!empty($query->highlight))
		{
			$this->document->setMetadata('keywords', implode(', ', $query->highlight));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Add feed link to the document head.
		if ($this->params->get('show_feed_link', 1) == 1)
		{
			// Add the RSS link.
			$props = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$route = JRoute::_($this->query->toUri() . '&format=feed&type=rss');
			$this->document->addHeadLink($route, 'alternate', 'rel', $props);

			// Add the ATOM link.
			$props = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$route = JRoute::_($this->query->toUri() . '&format=feed&type=atom');
			$this->document->addHeadLink($route, 'alternate', 'rel', $props);
		}
	}
}
