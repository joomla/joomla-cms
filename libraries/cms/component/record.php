<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Object representing a component extension record
 *
 * @since  __DEPLOY_VERSION__
 * @note   As of 4.0 this class will no longer extend JObject
 */
class JComponentRecord extends JObject
{
	/**
	 * Primary key
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $id;

	/**
	 * The component name
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $option;

	/**
	 * The component parameters
	 *
	 * @var    string|Registry
	 * @since  __DEPLOY_VERSION__
	 * @note   This field is protected to require reading this field to proxy through the getter to convert the params to a Registry instance
	 */
	protected $params;

	/**
	 * The component manifest
	 *
	 * @var    string|Registry
	 * @since  __DEPLOY_VERSION__
	 * @note   This field is protected to require reading this field to proxy through the getter to convert the params to a Registry instance
	 */
	protected $manifest;

	/**
	 * Indicates if this component is enabled
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $enabled;

	/**
	 * Class constructor
	 *
	 * @param   array  $data  The component record data to load
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * Returns the component manifest cache
	 *
	 * @return  Registry
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getManifest()
	{
		if (!($this->manifest instanceof Registry))
		{
			$this->manifest = new Registry($this->manifest);
		}

		return $this->manifest;
	}

	/**
	 * Sets the menu item parameters
	 *
	 * @param   Registry|string  $manifest  The data to be stored as the parameters
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setManifest($manifest)
	{
		$this->manifest = $manifest;
	}
}
