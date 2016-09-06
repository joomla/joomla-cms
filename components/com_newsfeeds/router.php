<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_newsfeeds
 *
 * @since  3.3
 */
class NewsfeedsRouter extends JComponentRouterView
{
	/**
	 * Newsfeeds Component router constructor
	 * 
	 * @param   JApplicationCms  $app   The application object
	 * @param   JMenu            $menu  The menu object to work with
	 */
	public function __construct($app = null, $menu = null)
	{
		$categories = new JComponentRouterViewconfiguration('categories');
		$categories->setKey('id');
		$this->registerView($categories);
		$category = new JComponentRouterViewconfiguration('category');
		$category->setKey('id')->setParent($categories, 'catid')->setNestable();
		$this->registerView($category);
		$newsfeed = new JComponentRouterViewconfiguration('newsfeed');
		$newsfeed->setKey('id')->setParent($category, 'catid');
		$this->registerView($newsfeed);

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));

		$params = JComponentHelper::getParams('com_content');

		if ($params->get('sef_advanced', 0))
		{
			$this->attachRule(new JComponentRouterRulesStandard($this));
		}
		else
		{
			require_once JPATH_SITE . '/components/com_newsfeeds/helpers/legacyrouter.php';
			$this->attachRule(new NewsfeedsRouterRulesLegacy($this));
		}
	}

	/**
	 * Method to get the segment(s) for a category
	 * 
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is build right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getCategorySegment($id, $query)
	{
		$category = JCategories::getInstance($this->getName())->get($id);
		if ($category)
		{
			return array_reverse($category->getPath());
		}

		return array();
	}

	/**
	 * Method to get the segment(s) for a category
	 * 
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is build right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getCategoriesSegment($id, $query)
	{
		return $this->getCategorySegment($id, $query);
	}

	/**
	 * Method to get the segment(s) for a newsfeed
	 * 
	 * @param   string  $id     ID of the newsfeed to retrieve the segments for
	 * @param   array   $query  The request that is build right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getNewsfeedSegment($id, $query)
	{
		return array($id);
	}

	/**
	 * Method to get the id for a category
	 * 
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getCategoryId($segment, $query)
	{
		if (isset($query['id']))
		{
			$category = JCategories::getInstance($this->getName())->get($query['id']);

			foreach ($category->getChildren() as $child)
			{
				if ($child->id == (int) $segment)
				{
					return $child->id;
				}
			}
		}

		return false;
	}

	/**
	 * Method to get the segment(s) for a category
	 * 
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * 
	 * @return  mixed   The id of this item or false
	 */
	public function getCategoriesId($segment, $query)
	{
		return $this->getCategoryId($segment, $query);
	}

	/**
	 * Method to get the segment(s) for a newsfeed
	 * 
	 * @param   string  $segment  Segment of the newsfeed to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * 
	 * @return  mixed   The id of this item or false
	 */
	public function getNewsfeedId($segment, $query)
	{
		return (int) $segment;
	}
}

/**
 * newsfeedsBuildRoute
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  The segments of the URL to parse.
 *
 * @return array
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function newsfeedsBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new NewsfeedsRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * newsfeedsParseRoute
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return array
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function newsfeedsParseRoute($segments)
{
	$app = JFactory::getApplication();
	$router = new NewsfeedsRouter($app, $app->getMenu());

	return $router->parse($segments);
}
