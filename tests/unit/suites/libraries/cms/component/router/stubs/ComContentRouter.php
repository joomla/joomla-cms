<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class of com_content
 *
 * @since  3.3
 */
class ContentRouterStandardRuleOnly extends JComponentRouterView
{
	protected $noIDs = false;
	protected $name = 'content';

	/**
	 * Content Component router constructor
	 *
	 * @param   JApplicationCms  $app    The application object
	 * @param   JMenu            $menu   The menu object to work with
	 * @param   boolean          $noIds  Should ID's be present in the URL or not?
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($app = null, $menu = null, $noIds)
	{
		$this->noIDs = $noIds;
		$categories = new JComponentRouterViewconfiguration('categories');
		$categories->setKey('id');
		$this->registerView($categories);
		$category = new JComponentRouterViewconfiguration('category');
		$category->setKey('id')->setParent($categories, 'catid')->setNestable()->addLayout('blog');
		$this->registerView($category);
		$article = new JComponentRouterViewconfiguration('article');
		$article->setKey('id')->setParent($category, 'catid');
		$this->registerView($article);
		$this->registerView(new JComponentRouterViewconfiguration('archive'));
		$this->registerView(new JComponentRouterViewconfiguration('featured'));
		$this->registerView(new JComponentRouterViewconfiguration('form'));

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesStandard($this));
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
			$path = array_reverse($category->getPath(), true);
			$path[0] = '1:root';

			if ($this->noIDs)
			{
				foreach ($path as &$segment)
				{
					list($id, $segment) = explode(':', $segment, 2);
				}
			}

			return $path;
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
	 * Method to get the segment(s) for an article
	 *
	 * @param   string  $id     ID of the article to retrieve the segments for
	 * @param   array   $query  The request that is build right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getArticleSegment($id, $query)
	{
		if (!strpos($id, ':'))
		{
			$db = JFactory::getDbo();
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('alias'))
				->from($dbquery->qn('#__content'))
				->where('id = ' . $dbquery->q($id));
			$db->setQuery($dbquery);

			$id .= ':' . $db->loadResult();
		}

		if ($this->noIDs)
		{
			list($void, $segment) = explode(':', $id, 2);

			return array($void => $segment);
		}

		return array((int) $id => $id);
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
				if ($this->noIDs)
				{
					if ($child->alias == $segment)
					{
						return $child->id;
					}
				}
				else
				{
					if ($child->id == (int) $segment)
					{
						return $child->id;
					}
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
	 * Method to get the segment(s) for an article
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getArticleId($segment, $query)
	{
		if ($this->noIDs)
		{
			$db = JFactory::getDbo();
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('id'))
				->from($dbquery->qn('#__content'))
				->where('alias = ' . $dbquery->q($segment))
				->where('catid = ' . $dbquery->q($query['id']));
			$db->setQuery($dbquery);

			return (int) $db->loadResult();
		}

		return (int) $segment;
	}
}
