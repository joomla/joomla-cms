<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.extension.plugin' );
require_once(dirname(__FILE__).DS.'joomla'.DS.'methods.php');

$dispatcher =& JEventDispatcher::getInstance();
$dispatcher->attach(new JoomlaXMLRPC($dispatcher));

/**
 * Joomla! Base XML-RPC Plugin
 *
 * @author Louis Landry <louis.landry@joomla.org>
 * @package XML-RPC
 * @since 1.5
 */
class JoomlaXMLRPC extends JPlugin {

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @since 1.5
	 */
	function JoomlaXMLRPC(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * Get available web services for this plugin
	 *
	 * @access	public
	 * @return	array	Array of web service descriptors
	 * @since	1.5
	 */
	function onGetWebServices()
	{
		// Initialize variables
		$services = array();

		// Site search service
		$services['joomla.searchSite'] = array(
			'function' => 'JoomlaXMLRPCServices::searchSite',
			'docstring' => 'Searches a remote site.',
			'signature' => array(array('string', 'string', 'string'))
			);

		return $services;
	}
}
?>