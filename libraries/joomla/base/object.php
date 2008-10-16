<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Object class, allowing __construct in PHP4.
 *
 * @package		Joomla.Framework
 * @subpackage	Base
 * @since		1.5
 */
abstract class JObject extends JStdClass
{

	/**
	 * An array of errors
	 *
	 * @var		array of error messages or JExceptions objects
	 * @access	protected
	 * @since	1.0
	 */
	protected $_errors = array();

	/**
	 * Class constructor, overridden in descendant classes.
	 *
	 * @access	protected
	 * @since	1.5
	 */
	protected function __construct() {}

	/**
	 * Provides interception of default php error handling logic for objects.  Enforceing class definitions
	 *
	 * @access	public
	 * @throw Jexception
	 * @since	1.6
 	 */
	public function __get($var) {
		throw new JException('Attempted to access undefined object variable', 0, E_NOTICE, $var, true);
	}

	/**
	 * Provides interception of default php error handling logic for objects.  Enforceing class definitions
	 *
	 * @access	public
	 * @throw Jexception
	 * @since	1.6
 	 */
	public function __set($var, $val) {
		throw new JException('Attempted to set undefined object variable', 0, E_NOTICE, array($var, $val), true);
	}

	/**
	 * Provides interception of default php error handling logic for objects.  Enforceing class definitions
	 *
	 * @access	public
	 * @throw Jexception
	 * @since	1.6
 	 */
	public function __call($func, $args) {
		throw new JException('Attempted to call non-existant method on object',0, E_ERROR, array($func, $args), true);
	}
}
