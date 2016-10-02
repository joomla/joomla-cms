<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_contact
 *
 * @since  3.3
 */
class ContactRouter extends JComponentRouterView
{
	/**
	 * Search Component router constructor
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
		$contact = new JComponentRouterViewconfiguration('contact');
		$contact->setKey('id')->setParent($category, 'catid');
		$this->registerView($contact);
		$this->registerView(new JComponentRouterViewconfiguration('featured'));

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));

		$params = JComponentHelper::getParams('com_content');

		if ($params->get('sef_advanced', 0))
		{
			$this->attachRule(new JComponentRouterRulesStandard($this));
		}
		else
		{
			require_once JPATH_SITE . '/components/com_contact/helpers/legacyrouter.php';
			$this->attachRule(new ContactRouterRulesLegacy($this));
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
	 * Method to get the segment(s) for a contact
	 * 
	 * @param   string  $id     ID of the contact to retrieve the segments for
	 * @param   array   $query  The request that is build right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getContactSegment($id, $query)
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
	 * Method to get the segment(s) for a contact
	 * 
	 * @param   string  $segment  Segment of the contact to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * 
	 * @return  mixed   The id of this item or false
	 */
	public function getContactId($segment, $query)
	{
		return (int) $segment;
	}
}

/**
 * Contact router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function ContactBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new ContactRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * Contact router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function ContactParseRoute($segments)
{
	$app = JFactory::getApplication();
	$router = new ContactRouter($app, $app->getMenu());

	return $router->parse($segments);
}
