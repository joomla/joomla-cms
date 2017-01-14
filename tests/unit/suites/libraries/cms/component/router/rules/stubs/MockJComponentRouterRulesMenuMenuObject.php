<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Mock Menu class for JComponentRouterRulesMenu class
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class MockJComponentRouterRulesMenuMenuObject
{
	/**
	 * Menuitems
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Active menuitem
	 *
	 * @var integer
	 */
	public $active = 49;

	/**
	 * Constructor for this object
	 *
	 * @since 3.4
	 */
	public function __construct()
	{
		$this->items[47] = (object) array(
			'id'           => '47',
			'menutype'     => 'testmenu',
			'title'        => 'Content Home',
			'alias'        => 'content-home',
			'route'        => 'content-home',
			'link'         => 'index.php?option=com_content&view=featured',
			'type'         => 'component',
			'level'        => '1',
			'language'     => '*',
			'component_id' => '22',
			'component'    => 'com_content',
			'parent_id'    => '0',
			'query'        => array('option' => 'com_content', 'view' => 'featured'));

		$this->items[48] = (object) array(
			'id'           => '48',
			'menutype'     => 'testmenu',
			'title'        => 'Categories View',
			'alias'        => 'categories',
			'route'        => 'content-home/categories',
			'link'         => 'index.php?option=com_content&view=categories&id=14',
			'type'         => 'component',
			'level'        => '2',
			'language'     => '*',
			'component_id' => '22',
			'component'    => 'com_content',
			'parent_id'    => '47',
			'query'        => array('option' => 'com_content', 'view' => 'categories', 'id' => '14'));

		$this->items[49] = (object) array(
			'id'           => '49',
			'menutype'     => 'testmenu',
			'title'        => 'Category View',
			'alias'        => 'category-view',
			'route'        => 'category-view',
			'link'         => 'index.php?option=com_content&view=category&id=20',
			'type'         => 'component',
			'level'        => '1',
			'language'     => '*',
			'component_id' => '22',
			'component'    => 'com_content',
			'parent_id'    => '47',
			'query'        => array('option' => 'com_content', 'view' => 'category', 'id' => '20'));

		$this->items[50] = (object) array(
			'id'           => '50',
			'menutype'     => 'testmenu',
			'title'        => 'Categories View',
			'alias'        => 'categories',
			'route'        => 'content-home/categories',
			'link'         => 'index.php?option=com_content&view=categories&id=14',
			'type'         => 'component',
			'level'        => '2',
			'language'     => 'en-GB',
			'component_id' => '22',
			'component'    => 'com_content',
			'parent_id'    => '47',
			'query'        => array('option' => 'com_content', 'view' => 'categories', 'id' => '14'));

		$this->items[51] = (object) array(
			'id'           => '51',
			'menutype'     => 'testmenu',
			'title'        => 'Content Home',
			'alias'        => 'content-home',
			'route'        => 'content-home',
			'link'         => 'index.php?option=com_content&view=featured',
			'type'         => 'component',
			'level'        => '1',
			'language'     => 'en-GB',
			'component_id' => '22',
			'component'    => 'com_content',
			'parent_id'    => '0',
			'query'        => array('option' => 'com_content', 'view' => 'featured'));
	}

	/**
	 * Gets the menu item set that fits the search array
	 *
	 * @param   array   $attributes  Search criteria
	 * @param   array   $values      Search criteria
	 *
	 * @return  mixed  Menu items
	 *
	 * @since   3.4
	 */
	public function getItems($attributes, $values)
	{
		$items = array();
		$attributes = (array) $attributes;
		$values = (array) $values;

		foreach ($this->items as $item)
		{
			$test = true;

			for ($i = 0, $count = count($attributes); $i < $count; $i++)
			{
				if (is_array($values[$i]))
				{
					if (!in_array($item->{$attributes[$i]}, $values[$i]))
					{
						$test = false;
						break;
					}
				}
				else
				{
					if ($item->{$attributes[$i]} != $values[$i])
					{
						$test = false;
						break;
					}
				}
			}

			if ($test)
			{
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Return the currently active menuitem
	 *
	 * @return  object  Menuitem
	 * @since 3.4
	 */
	public function getActive()
	{
		return (isset($this->items[$this->active]) ? $this->items[$this->active] : null);
	}

	/**
	 * Return the default menuitem for the language
	 *
	 * @param   string  $language  Language for the default
	 *
	 * @return  object  Menuitem
	 * @since 3.4
	 */
	public function getDefault($language = '*')
	{
		if ($language == '*')
		{
			return $this->items[47];
		}

		if ($language == 'en-GB')
		{
			return $this->items[51];
		}
	}
}
