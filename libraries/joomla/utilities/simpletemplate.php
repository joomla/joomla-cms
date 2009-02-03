<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

/**
 * Text Template Class
 *
 * This class implements an API for constructing and populating
 * simple templates with data.
 *
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @since		1.6
 */
class JSimpleTemplate extends JObject
{
	/**
	 * The working title of the template.
	 *
	 * @access	public
	 * @var		string
	 */
	var $title;

	/**
	 * The working body of the template.
	 *
	 * @access	public
	 * @var		string
	 */
	var $body;

	/**
	 * The raw template.
	 *
	 * @access	protected
	 * @var		object
	 */
	var $_template;

	/**
	 * The template options.
	 *
	 * @access	private
	 * @var		array
	 */
	var $_options;

	/**
	 * Method to get an instance of the template.
	 *
	 * @access	public
	 * @param	string		$name		The name of the template to load.
	 * @param	array		$options	An array of options to pass to the template.
	 * @return	object		A JSimpleTemplate object.
	 */
	function &getInstance($name, $options = array())
	{
		static $instances;

		if ($instances == null) {
			$instances = array();
		}

		// Only load the template once.
		if (!isset($instances[$name]))
		{
			// Instantiate the template.
			$instances[$name] = new JSimpleTemplate($options);
			$instances[$name]->load($name);
		}

		return $instances[$name];
	}

	/**
	 * Method to construct the object on instantiation.
	 *
	 * @access	protected
	 * @param	array		$options	An array of form options.
	 * @return	void
	 * @since	1.0
	 */
	function __construct($options = array())
	{
		$this->_template	= new JObject();
		$this->_options		= array();
	}

	/**
	 * Method to get the working title of the template.
	 *
	 * @access	public
	 * @return	string		The working title of the template.
	 * @since	1.0
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Method to set the working title of the template.
	 *
	 * @access	public
	 * @param	string		$title		The new title of the template.
	 * @return	void
	 * @since	1.0
	 */
	function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Method to get the working body of the template.
	 *
	 * @access	public
	 * @return	string		The working body of the template.
	 * @since	1.0
	 */
	function getBody()
	{
		return $this->body;
	}

	/**
	 * Method to set the working body of the template.
	 *
	 * @access	public
	 * @param	string		$body		The new body of the template.
	 * @return	void
	 * @since	1.0
	 */
	function setBody($body)
	{
		$this->body = $body;
	}

	/**
	 * Method to load a template from the database.
	 *
	 * @access	public
	 * @param	string		$name		The name of the template to load.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.1
	 */
	function load($name)
	{
		// Load the template from the database.
		$db = &JFactory::getDBO();
		$db->setQuery(
			'SELECT * FROM `#__simple_templates`' .
			' WHERE `name` = '.$db->Quote($name)
		);
		$template = $db->loadAssoc();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Bind the template.
		if (!empty($template)) {
			jimport('joomla.utilities.arrayhelper');
			$this->_template = JArrayHelper::toObject($template, 'JObject');
		}

		return true;
	}

	/**
	 * Method to bind data to the template.
	 *
	 * @access	public
	 * @param	mixed		$data		An array/object of values to bind to the template.
	 * @param	string		$prefix		An optional prefix for the template keys.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.1
	 */
	function bind($data, $prefix = '')
	{
		// The data must be an object or array.
		if (!is_object($data) && !is_array($data)) {
			return false;
		}

		// Convert objects to arrays.
		if (is_object($data))
		{
			// Handle a JObject.
			if (is_a($data, 'JObject')) {
				$data = $data->getProperties();
			}
			// Handle other types of objects.
			else {
				$data = (array)$data;
			}
		}

		// Grab a copy of the template title and body.
		$title	= $this->_template->get('title');
		$body	= $this->_template->get('body');

		// Bind the data to the template.
		foreach ($data as $key => $value)
		{
			// Check that the value is scalar.
			if (is_scalar($value))
			{
				// Replace the template place holders with values.
				$key	= strtoupper('{'.$prefix.$key.'}');
				$title	= str_replace($key, $value, $title);
				$body	= str_replace($key, $value, $body);
			}
		}

		// Push out the new title and body.
		$this->title 	= $title;
		$this->body		= $body;

		return true;
	}
}