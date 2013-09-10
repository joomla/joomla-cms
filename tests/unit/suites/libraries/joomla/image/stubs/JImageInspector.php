<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the JImage class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Media
 * @since       11.3
 */
class JImageInspector extends JImage
{
	/**
	 * @var    JImageFilter  A mock image filter to be returned from getFilterInstance().
	 * @since  11.3
	 */
	public $mockFilter;

	/**
	 * Method for inspecting protected variables.
	 *
	 * @param   string  $name  The name of the property.
	 *
	 * @return  mixed  The value of the class variable.
	 *
	 * @since   11.3
	 */
	public function getClassProperty($name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			throw new Exception('Undefined or private property: ' . __CLASS__ . '::' . $name);
		}
	}

	/**
	 * Method for setting protected variables.
	 *
	 * @param   string  $name   The name of the property.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void.
	 *
	 * @since   11.3
	 */
	public function setClassProperty($name, $value)
	{
		if (property_exists($this, $name))
		{
			$this->$name = $value;
		}
		else
		{
			throw new Exception('Undefined or private property: ' . __CLASS__ . '::' . $name);
		}
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $type  The image filter type to get.
	 *
	 * @return  JImageFilter
	 *
	 * @since   11.3
	 * @throws  RuntimeException
	 */
	public function getFilterInstance($type)
	{
		if ($this->mockFilter)
		{
			return $this->mockFilter;
		}
		else
		{
			return parent::getFilterInstance($type);
		}
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   mixed    $width        The width of the resized image in pixels or a percentage.
	 * @param   mixed    $height       The height of the resized image in pixels or a percentage.
	 * @param   integer  $scaleMethod  The method to use for scaling
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public function prepareDimensions($width, $height, $scaleMethod)
	{
		return parent::prepareDimensions($width, $height, $scaleMethod);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   mixed  $height  The input height value to sanitize.
	 * @param   mixed  $width   The input width value for reference.
	 *
	 * @return  integer
	 *
	 * @since   11.3
	 */
	public function sanitizeHeight($height, $width)
	{
		return parent::sanitizeHeight($height, $width);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   mixed  $offset  An offset value.
	 *
	 * @return  integer
	 *
	 * @since   11.3
	 */
	public function sanitizeOffset($offset)
	{
		return parent::sanitizeOffset($offset);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   mixed  $width   The input width value to sanitize.
	 * @param   mixed  $height  The input height value for reference.
	 *
	 * @return  integer
	 *
	 * @since   11.3
	 */
	public function sanitizeWidth($width, $height)
	{
		return parent::sanitizeWidth($width, $height);
	}
}
