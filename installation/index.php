<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

define('_JEXEC', 1);

define('JPATH_BASE', dirname(__FILE__));

define('DS', DIRECTORY_SEPARATOR);

try {
	require_once(JPATH_BASE . DS . 'includes' . DS . 'defines.php');
	require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');

	// create the mainframe object
	$mainframe = JFactory::getApplication('installation');

	// initialuse the application
	$mainframe->initialise();

	// render the application
	$mainframe->render();

	/**
	* RETURN THE RESPONSE
 	*/
	echo JResponse::toString();

}
catch (JException $e) {
	$e->set('level', E_ERROR);
	JError::throwError($e);
}
