<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class JHtmlElement
 * Simple class that allows you to create HTML output in an OOP manner
 *
 * @since  3.5
 */
class JHtmlElement
{
	/**
	 * Element Tag Name. I.E. div, p, a etc...
	 * @var string
	 */
	protected $tagName;

	/**
	 * Associate array of attributes in 'propertyName' => value format
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Associate array of class names in 'className' => 'className' format
	 * @var array
	 */
	protected $classes = array();

	/**
	 * Element innerHtml
	 * @var array
	 */
	protected $innerHtml = array();

	/**
	 * Constructor
	 *
	 * @param   string  $tagName     the name of the HTML element I.E. div, p, a
	 * @param   array   $attributes  associative array of attributes Format: array('propertyName' => 'propertyValue')
	 * @param   array   $innerHtml   This can be a mixed array of string values and JHtmlElement objects
	 */
	public function __construct($tagName, $attributes = array(), $innerHtml = array())
	{
		$this->tagName = $this->escape($tagName);
		$this->setAttributes($attributes);
		$this->setInnerHtml($innerHtml);
	}

	/**
	 * Method to escape HTML special characters.
	 * Also trims whitespace from the string
	 *
	 * @param   string  $string  to escape
	 *
	 * @return string
	 */
	protected function escape($string)
	{
		return htmlspecialchars(trim($string), ENT_COMPAT, 'UTF-8', false);
	}

	/**
	 * Method to set the attributes of the element
	 *
	 * @param   array  $attributes  associative array 'name' => 'value' format
	 *
	 * @return $this to allow chaining
	 */
	public function setAttributes($attributes = array())
	{
		foreach ($attributes AS $name => $value)
		{
			$this->addAttribute($name, $value);
		}

		return $this;
	}

	/**
	 * Method to get the properties
	 *
	 * @param   bool  $toString  flag to return the properties in $name="$value" format
	 *
	 * @return array|string
	 */
	public function getAttributes($toString = true)
	{
		if (!$toString)
		{
			return $this->attributes;
		}

		if (empty($this->attributes))
		{
			return null;
		}

		$attributes = '';
		foreach ($this->attributes AS $name => $value)
		{
			$attributes .= ' ' . $name . '="' . $value . '"';
		}

		return ' ' . trim($attributes);
	}

	/**
	 * Method to add attributes to the element.
	 *
	 * @param   string  $name   attribute name
	 * @param   mixed   $value  attribute value
	 *
	 * @return $this to allow chaining
	 */
	public function addAttribute($name, $value)
	{
		// Classes are a special property, so we don't handle them here.
		if ($name == 'class')
		{
			$this->setClasses($value);

			return $this;
		}

		if (!is_numeric($name))
		{
			$this->attributes[$this->escape($name)] = $value;
		}

		return $this;
	}

	/**
	 * Method to check if an attribute exists in the the attributes array
	 *
	 * @param   string  $name  of the attribute
	 *
	 * @return bool
	 */
	public function hasAttribute($name)
	{
		return array_key_exists($this->escape($name), $this->attributes);
	}

	/**
	 * Method to remove a attribute by name
	 *
	 * @param   string  $name  of the attribute to remove
	 *
	 * @return $this to allow chaining
	 */
	public function removeAttribute($name)
	{
		unset($this->attributes[$name]);

		return $this;
	}

	/**
	 * Method to set the css classes of this element
	 *
	 * @param   array  $classes  array('className','className') format
	 *
	 * @return $this to allow chaining
	 */
	public function setClasses($classes = array())
	{
		if (!is_array($classes))
		{
			$classes = explode(' ', $classes);
		}

		foreach ($classes AS $className)
		{
			$this->addClass($className);
		}
	}

	/**
	 * Method to get the classes
	 *
	 * @param   bool  $toString  flag to return the properties in class="$classes[0] $classes[1] etc..." format
	 *
	 * @return array|string
	 */
	public function getClasses($toString = true)
	{
		if (!$toString)
		{
			return $this->classes;
		}

		if (empty($this->classes))
		{
			return null;
		}

		return ' class="' . trim(implode(' ', $this->classes)) . '"';
	}

	/**
	 * Method to add css classes to the input
	 *
	 * @param   string  $className  The name of the class
	 *
	 * @return $this to allow for chaining
	 */
	public function addClass($className)
	{
		$cleanName = $this->escape($className);
		$this->classes[$cleanName] = trim($className);

		return $this;
	}

	/**
	 * Method to check if a CSS class exists in the classes array
	 *
	 * @param   string  $className  name of the class to look for
	 *
	 * @return bool
	 */
	public function hasClass($className)
	{
		return array_key_exists($this->escape($className), $this->classes);
	}

	/**
	 * Method to remove css classes from the input
	 *
	 * @param   string  $className  The name of the class
	 *
	 * @return $this to allow for chaining
	 */
	public function removeClass($className)
	{
		$cleanName = $this->escape($className);

		unset($this->classes[$cleanName]);

		return $this;
	}

	/**
	 * Method to set the innerHtml for an element
	 *
	 * @param   mixed  $innerHtml  string|JHtmlElement|mixed array of string|JHtmlElement
	 *
	 * @return $this to allow for chaining
	 */
	public function setInnerHtml($innerHtml)
	{
		if (!is_array($innerHtml))
		{
			$this->innerHtml[] = $innerHtml;

			return $this;
		}

		$this->innerHtml = array_merge($this->innerHtml, $innerHtml);

		return $this;
	}

	/**
	 * Method to empty the innerHtml array
	 *
	 * @return void
	 */
	public function clearInnerHtml()
	{
		$this->innerHtml = array();
	}

	/**
	 * Method to get the innerHtml of an element
	 *
	 * @param   bool  $toString  flag to return the innerHtml as a HTML String
	 *
	 * @return array|string
	 */
	public function getInnerHtml($toString = true)
	{
		if (!$toString)
		{
			return $this->innerHtml;
		}

		$innerHtml = '';
		foreach ($this->innerHtml AS $part)
		{
			if ($part instanceof JHtmlElement)
			{
				$part = $part->renderHtml();
			}

			$innerHtml .= $part;
		}

		return $innerHtml;
	}

	/**
	 * Method to add to the innerHtml
	 *
	 * @param   string|JHtmlElement  $innerHtml  This can be a string value or a JHtmlElement object
	 * @param   bool                 $before     Should $innerHtml be placed at the beginning or the end of $this->innerHtml?
	 *
	 * @return $this to allow chaining
	 */
	public function addInnerHtml($innerHtml, $before = false)
	{
		// Add it to the front
		if ($before)
		{
			array_unshift($this->innerHtml, $innerHtml);

			return $this;
		}

		array_push($this->innerHtml, $innerHtml);

		return $this;
	}

	/**
	 * Convenience method to add a new child element to the innerHtml array
	 *
	 * @param   string  $tagName     the type of tag to insert
	 * @param   array   $attributes  array of element attributes
	 * @param   array   $innerHtml   This can be a mixed array of string values and JHtmlElement objects.
	 *                                It will be used in the constructor for the returned element
	 * @param   bool    $before      Should the child be added before current content or after.
	 *
	 * @return JHtmlElement reference to the newly created child element
	 */
	public function addChild($tagName, $attributes = array(), $innerHtml = array(),$before = false)
	{
		$child = new JHtmlElement($tagName, $attributes, $innerHtml);
		$this->addInnerHtml($child, $before);

		return $child;
	}

	/**
	 * Magic method to render the class as a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->renderHtml();
	}

	/**
	 * Method to render object to HTML
	 *
	 * @return string HTML element
	 */
	protected function renderHtml()
	{
		$html = '<' . $this->tagName;
		$html .= $this->getClasses();
		$html .= $this->getAttributes();

		// Void elements don't have innerHtml
		if ($this->isVoidElement())
		{
			$html .= '/>';

			return $html;
		}

		$html .= '>';
		$html .= $this->getInnerHtml();
		$html .= '</' . $this->tagName . '>';

		return $html;
	}

	/**
	 * Method to check if the tagName is a HTML void element
	 *
	 * @return bool
	 */
	protected function isVoidElement()
	{
		$voidElements = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr',
						'img', 'input', 'keygen', 'link', 'meta', 'param', 'source',
						'track', 'wbr');

		return in_array($this->tagName, $voidElements);
	}
}
