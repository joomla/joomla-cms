<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Query;

/**
 * Query Element Class.
 *
 * @since  1.0
 */
class QueryElement
{
	/**
	 * The name of the element.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $name;

	/**
	 * An array of elements.
	 *
	 * @var    string[]
	 * @since  1.0
	 */
	protected $elements = [];

	/**
	 * Glue piece.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $glue;

	/**
	 * Constructor.
	 *
	 * @param   string           $name      The name of the element.
	 * @param   string[]|string  $elements  String or array.
	 * @param   string           $glue      The glue for elements.
	 *
	 * @since   1.0
	 */
	public function __construct($name, $elements, $glue = ',')
	{
		$this->name = $name;
		$this->glue = $glue;

		$this->append($elements);
	}

	/**
	 * Magic function to convert the query element to a string.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function __toString()
	{
		if (substr($this->name, -2) === '()')
		{
			return \PHP_EOL . substr($this->name, 0, -2) . '(' . implode($this->glue, $this->elements) . ')';
		}

		return \PHP_EOL . $this->name . ' ' . implode($this->glue, $this->elements);
	}

	/**
	 * Appends element parts to the internal list.
	 *
	 * @param   string[]|string  $elements  String or array.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function append($elements)
	{
		if (\is_array($elements))
		{
			$this->elements = array_merge($this->elements, $elements);
		}
		else
		{
			$this->elements = array_merge($this->elements, [$elements]);
		}
	}

	/**
	 * Gets the elements of this element.
	 *
	 * @return  string[]
	 *
	 * @since   1.0
	 */
	public function getElements()
	{
		return $this->elements;
	}

	/**
	 * Gets the glue of this element.
	 *
	 * @return  string  Glue of the element.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getGlue()
	{
		return $this->glue;
	}

	/**
	 * Gets the name of this element.
	 *
	 * @return  string  Name of the element.
	 *
	 * @since   1.7.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets the name of this element.
	 *
	 * @param   string  $name  Name of the element.
	 *
	 * @return  $this
	 *
	 * @since   1.3.0
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Method to provide basic copy support.
	 *
	 * Any object pushed into the data of this class should have its own __clone() implementation.
	 * This method does not support copying objects in a multidimensional array.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function __clone()
	{
		foreach ($this as $k => $v)
		{
			if (\is_object($v))
			{
				$this->{$k} = clone $v;
			}
			elseif (\is_array($v))
			{
				foreach ($v as $i => $element)
				{
					if (\is_object($element))
					{
						$this->{$k}[$i] = clone $element;
					}
				}
			}
		}
	}
}
