<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Menu;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Object representing a menu item
 *
 * @since  3.7.0
 * @note   This class will no longer extend stdClass in Joomla 4
 */
class MenuItem extends \stdClass
{
	/**
	 * Primary key
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $id;

	/**
	 * The type of menu this item belongs to
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $menutype;

	/**
	 * The display title of the menu item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $title;

	/**
	 * The SEF alias of the menu item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $alias;

	/**
	 * A note associated with the menu item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $note;

	/**
	 * The computed path of the menu item based on the alias field, this is populated from the `path` field in the `#__menu` table
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $route;

	/**
	 * The actual link the menu item refers to
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $link;

	/**
	 * The type of link
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $type;

	/**
	 * The relative level in the tree
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $level;

	/**
	 * The assigned language for this item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $language;

	/**
	 * The click behaviour of the link
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $browserNav;

	/**
	 * The access level required to view the menu item
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $access;

	/**
	 * The menu item parameters
	 *
	 * @var    string|Registry
	 * @since  3.7.0
	 * @note   This field is protected to require reading this field to proxy through the getter to convert the params to a Registry instance
	 */
	protected $params;

	/**
	 * Indicates if this menu item is the home or default page
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $home;

	/**
	 * The image of the menu item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $img;

	/**
	 * The optional template style applied to this menu item
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $template_style_id;

	/**
	 * The extension ID of the component this menu item is for
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $component_id;

	/**
	 * The parent menu item in the menu tree
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $parent_id;

	/**
	 * The name of the component this menu item is for
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $component;

	/**
	 * The tree of parent menu items
	 *
	 * @var    array
	 * @since  3.7.0
	 */
	public $tree = array();

	/**
	 * An array of the query string values for this item
	 *
	 * @var    array
	 * @since  3.7.0
	 */
	public $query = array();

	/**
	 * Class constructor
	 *
	 * @param   array  $data  The menu item data to load
	 *
	 * @since   3.7.0
	 */
	public function __construct($data = array())
	{
		foreach ((array) $data as $key => $value)
		{
			$this->$key = $value;
		}
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.7.0
	 * @deprecated  4.0  Access the item parameters through the `getParams()` method
	 */
	public function __get($name)
	{
		if ($name === 'params')
		{
			return $this->getParams();
		}

		return $this->get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 * @deprecated  4.0  Set the item parameters through the `setParams()` method
	 */
	public function __set($name, $value)
	{
		if ($name === 'params')
		{
			$this->setParams($value);

			return;
		}

		$this->set($name, $value);
	}

	/**
	 * Method check if a certain otherwise inaccessible properties of the form field object is set.
	 *
	 * @param   string  $name  The property name to check.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.1
	 * @deprecated  4.0 Deprecated without replacement
	 */
	public function __isset($name)
	{
		if ($name === 'params')
		{
			return !($this->params instanceof Registry);
		}

		return $this->get($name) !== null;
	}

	/**
	 * Returns the menu item parameters
	 *
	 * @return  Registry
	 *
	 * @since   3.7.0
	 */
	public function getParams()
	{
		if (!($this->params instanceof Registry))
		{
			try
			{
				$this->params = new Registry($this->params);
			}
			catch (\RuntimeException $e)
			{
				/*
				 * Joomla shipped with a broken sample json string for 4 years which caused fatals with new
				 * error checks. So for now we catch the exception here - but one day we should remove it and require
				 * valid JSON.
				 */
				$this->params = new Registry;
			}
		}

		return $this->params;
	}

	/**
	 * Sets the menu item parameters
	 *
	 * @param   Registry|string  $params  The data to be stored as the parameters
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed    The value of the property.
	 *
	 * @since   3.7.0
	 * @deprecated  4.0
	 */
	public function get($property, $default = null)
	{
		if (isset($this->$property))
		{
			return $this->$property;
		}

		return $default;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set.
	 *
	 * @return  mixed  Previous value of the property.
	 *
	 * @since   3.7.0
	 * @deprecated  4.0
	 */
	public function set($property, $value = null)
	{
		$previous = isset($this->$property) ? $this->$property : null;
		$this->$property = $value;

		return $previous;
	}
}
