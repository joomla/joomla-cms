<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Button base class
 *
 * The JButton is the base class for all JButton types
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
abstract class JButton extends JObject
{
	/**
	 * Element name
	 *
	 * This has to be set in the final renderer classes.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_name = null;

	/**
	 * Reference to the object that instantiated the element
	 *
	 * @var    object
	 * @since  11.1
	 */
	protected $_parent = null;

	/**
	 * Constructor
	 * 
	 * @param   object  $parent  The parent
	 */
	public function __construct($parent = null)
	{
		$this->_parent = $parent;
	}

	/**
	 * Get the element name
	 *
	 * @return  string   Name of the button
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get the HTML to render the button
	 * 
	 * @param   array  $definition  Parameters to be passed
	 *
	 * @return  string
	 */
	public function render(&$definition)
	{
		/*
		 * Initialise some variables
		 */
		$html = null;
		$id = call_user_func_array(array(&$this, 'fetchId'), $definition);
		$action = call_user_func_array(array(&$this, 'fetchButton'), $definition);

		// Build id attribute
		if ($id)
		{
			$id = "id=\"$id\"";
		}

		// Build the HTML Button
		$html .= "<li class=\"button\" $id>\n";
		$html .= $action;
		$html .= "</li>\n";

		return $html;
	}

	/**
	 * Method to get the CSS class name for an icon identifier
	 *
	 * Can be redefined in the final class
	 *
	 * @param   string   $identifier  Icon identification string
	 *
	 * @return  string   CSS class name
	 *
	 * @since   11.1
	 */
	public function fetchIconClass($identifier)
	{
		return "icon-32-$identifier";
	}

	/**
	 * Get the button
	 *
	 * Defined in the final button class
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	abstract public function fetchButton();
}