<?php
/**
* @version $Id: app.php 1534 2005-12-22 01:38:31Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Base class for a Joomla Model
*
* Acts as a Factory class for application specific objects and
* provides many supporting API functions.
*
* @abstract
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.1
*/
class JModel extends JObject {
	/**
	 * Database Connector
	 *
	 * @var object
	 */
	var $_db;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT
	 * references. This causes problems with cross-referencing.
	 *
	 * @param object $subject The object to observe
	 * @since 1.1
	 */
	function JModel(&$dbo) {
		parent::__construct();
		$this->_db = &$dbo;
	}

	function &getDBO() {
		return $this->_db;
	}
}
?>