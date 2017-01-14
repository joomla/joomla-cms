<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for JComponentRouterView
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class JComponentRouterViewInspector extends JComponentRouterView
{
	/**
	 * Gets an attribute of the object
	 * 
	 * @param   string   $key  Attributename to return
	 *
	 * @return  mixed  Attributes of the object
	 *
	 * @since   3.4
	 */
	public function get($key)
	{
		return $this->$key;
	}

	/**
	 * Sets an attribute of the object
	 * 
	 * @param   string   $key    Attributename to return
	 * @param   mixed    $value  Value to be set
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function set($key, $value)
	{
		$this->$key = $value;
	}

	/**
	* Get content items of the type category
	*
	* @param int $id ID of the category to load
	*
	* @return array  Categories path identified by $id
	*
	* @since 3.4
	*/
	public function getCategorySegment($id, $query)
	{
		$category = JCategories::getInstance($this->getName())->get($id);

		if ($category)
		{
			return array_reverse($category->getPath(), true);
		}

		return array();
	}

	/**
	* Get content items of the type categories
	*
	* @param int $id ID of the category to load
	*
	* @return array  Categories path identified by $id
	*
	* @since 3.4
	*/
	public function getCategoriesSegment($id, $query)
	{
		return $this->getCategorySegment($id, $query);
	}

	/**
	* Get content items of the type article
	*
	* @param int $id ID of the article to load
	*
	* @return array article identified by $id
	*
	* @since 3.4
	*/
	public function getArticleSegment($id, $query)
	{
		return array((int) $id => $id);
	}
}

/**
 * Mock class to crash JComponentRouterAdvanced::getName
 */
class FakeComponentURLCreator extends JComponentRouterViewInspector
{
}
