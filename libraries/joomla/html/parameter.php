<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// No direct access
defined('JPATH_BASE') or die();

jimport('joomla.html.form');

//Register the element class with the loader
JLoader::register('JElement', dirname(__FILE__).DS.'parameter'.DS.'element.php');

/**
 * Parameter handler
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */
class JParameter extends JForm
{
	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	string The raw parms text
	 * @param	string Path to the xml setup file
	 * @since	1.5
	 */
	public function __construct($data, $path = '', $addDefault = false)
	{
		parent::__construct($data, $path, 'param', $addDefault);
	}

	/**
	 * Render
	 *
	 * @access	public
	 * @param	string	The name of the control, or the default text area if a setup file is not found
	 * @return	string	HTML
	 * @since	1.5
	 */
	public function render($name = 'params', $group = '_default', $chrome = 'params', $form = true)
	{
		return parent::render($name, $group, $chrome, $form);
	}

	/**
	 * Render all parameters to an array
	 *
	 * @access	public
	 * @param	string	The name of the control, or the default text area if a setup file is not found
	 * @return	array	Array of all parameters, each as array Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */
	public function renderToArray($name = 'params', $group = '_default')
	{
		return parent::renderToArray($name, $group);
	}

	/**
	 * Return number of params to render
	 *
	 * @access	public
	 * @return	mixed	Boolean falst if no params exist or integer number of params that exist
	 * @since	1.5
	 */
	public function getNumParams($group = '_default')
	{
		return parent::getNumElements($group);
	}

	/**
	 * Render all parameters
	 * Notice: This function does not support the conditional parameters introduced in 1.6.
	 * This functionality is implemented in the JParameter::render() function directly.
	 *
	 * @access	public
	 * @param	string	The name of the control, or the default text area if a setup file is not found
	 * @return	array	Aarray of all parameters, each as array Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */
	public function getParams($name = 'params', $group = '_default')
	{
		return parent::getElements($name, $group);
	}

	/**
	 * Render a parameter type
	 *
	 * @param	object	A param tag node
	 * @param	string	The control name
	 * @return	array	Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */
	public function getParam(&$node, $control_name = 'params', $group = '_default')
	{
		return parent::getElement($node, $control_name, $group);
	}
}
