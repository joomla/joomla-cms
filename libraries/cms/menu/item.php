<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Menu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Object representing a menu item
 *
 * @since  __DEPLOY_VERSION__
 */
class JMenuItem extends JObject
{
	/**
	 * Primary key
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $id;

	/**
	 * The type of menu this item belongs to
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $menutype;

	/**
	 * The display title of the menu item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $title;

	/**
	 * The SEF alias of the menu item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $alias;

	/**
	 * A note associated with the menu item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $note;

	/**
	 * The computed path of the menu item based on the alias field, this is populated from the `path` field in the `#__menu` table
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $route;

	/**
	 * The actual link the menu item refers to
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $link;

	/**
	 * The type of link
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $type;

	/**
	 * The relative level in the tree
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $level;

	/**
	 * The assigned language for this item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $language;

	/**
	 * The click behaviour of the link
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $browserNav;

	/**
	 * The access level required to view the menu item
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $access;

	/**
	 * The menu item parameters
	 *
	 * @var    string|Registry
	 * @since  __DEPLOY_VERSION__
	 * @note   This field is protected to require reading this field to proxy through the getter to convert the params to a Registry instance
	 */
	protected $params;

	/**
	 * Indicates if this menu item is the home or default page
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $home;

	/**
	 * The image of the menu item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $img;

	/**
	 * The optional template style applied to this menu item
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $template_style_id;

	/**
	 * The extension ID of the component this menu item is for
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $component_id;

	/**
	 * The parent menu item in the menu tree
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $parent_id;

	/**
	 * The name of the component this menu item is for
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $component;

	/**
	 * The tree of parent menu items
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public $tree = array();

	/**
	 * An array of the query string values for this item
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public $query = array();

	/**
	 * Class constructor
	 *
	 * @param   array  $data  The menu item data to load
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * Returns the menu item parameters
	 *
	 * @return  Registry
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getParams()
	{
		if (!($this->params instanceof Registry))
		{
			try
			{
				$this->params = new Registry($this->params);
			}
			catch (RuntimeException $e)
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}
}
