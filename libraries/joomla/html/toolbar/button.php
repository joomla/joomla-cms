<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Button base class
 *
 * The JButton is the base class for all JButton types
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage		HTML
 * @since		1.5
 */
abstract class JButton extends JObject
{
	/**
	 * element name
	 *
	 * This has to be set in the final renderer classes.
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $_name = null;

	/**
	 * reference to the object that instantiated the element
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $_parent = null;

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	public function __construct($parent = null)
	{
		$this->_parent = $parent;
	}

	/**
	 * get the element name
	 *
	 * @access	public
	 * @return	string	type of the parameter
	 */
	public function getName()
	{
		return $this->_name;
	}

	public function render(&$definition)
	{
		/*
		 * Initialise some variables
		 */
		$html	= null;
		$id		= call_user_func_array(array(&$this, 'fetchId'), $definition);
		$action	= call_user_func_array(array(&$this, 'fetchButton'), $definition);

		// Build id attribute
		if ($id) {
			$id = "id=\"$id\"";
		}

		// Build the HTML Button
		$html	.= "<li class=\"button\" $id>\n";
		$html	.= $action;
		$html	.= "</li>\n";

		return $html;
	}

	/**
	 * Method to get the CSS class name for an icon identifier
	 *
	 * Can be redefined in the final class
	 *
	 * @access	public
	 * @param	string	$identifier	Icon identification string
	 * @return	string	CSS class name
	 * @since	1.5
	 */
	public function fetchIconClass($identifier)
	{
		return "icon-32-$identifier";
	}

	/**
	 * Get the button id
	 *
	 * Can be redefined in the final button class
	 *
	 * @access		public
	 * @since		1.5
	 */
	public function fetchId()
	{
		return;
	}

	/**
	 * Get the button
	 *
	 * Defined in the final button class
	 *
	 * @abstract
	 * @access		public
	 * @since		1.5
	 */
	abstract public function fetchButton();
}
