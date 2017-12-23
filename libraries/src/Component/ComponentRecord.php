<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Object representing a component extension record
 *
 * @since  3.7.0
 */
class ComponentRecord
{
	/**
	 * Primary key
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $id;

	/**
	 * The component name
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $option;

	/**
	 * The component parameters
	 *
	 * @var    string|Registry
	 * @since  3.7.0
	 * @note   This field is protected to require reading this field to proxy through the getter to convert the params to a Registry instance
	 */
	protected $params;

	/**
	 * The extension namespace
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public $namespace;

	/**
	 * Indicates if this component is enabled
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $enabled;

	/**
	 * Class constructor
	 *
	 * @param   array  $data  The component record data to load
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
	 * @deprecated  5.0  Access the item parameters through the `getParams()` method
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
	 * @deprecated  5.0  Set the item parameters through the `setParams()` method
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
	 * @since   3.7.0
	 */
	public function getParams()
	{
		if (!($this->params instanceof Registry))
		{
			$this->params = new Registry($this->params);
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
}
