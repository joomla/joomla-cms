<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define('_JEXEC', 1);

define('JPATH_BASE', dirname(__FILE__));

define('DS', DIRECTORY_SEPARATOR);

try {
	require_once JPATH_BASE .DS.'includes'.DS.'defines.php';
	require_once JPATH_BASE .DS.'includes'.DS.'framework.php';

	JDEBUG ? $_PROFILER->mark('afterLoad') : null;

	/**
	 * CREATE THE APPLICATION
	 *
	 * NOTE :
	 */


	$mainframe =& JFactory::getApplication('site');

	/**
	 * INITIALISE THE APPLICATION
	 *
	 * NOTE :
	 */
	// set the language
	$mainframe->initialise();

	JPluginHelper::importPlugin('system');

	// trigger the onAfterInitialise events
	JDEBUG ? $_PROFILER->mark('afterInitialise') : null;
	$mainframe->triggerEvent('onAfterInitialise');

	/**
	 * ROUTE THE APPLICATION
	 *
	 * NOTE :
	 */
	$mainframe->route();

	// authorization
	$Itemid = JRequest::getInt('Itemid');
	$mainframe->authorize($Itemid);

	// trigger the onAfterRoute events
	JDEBUG ? $_PROFILER->mark('afterRoute') : null;
	$mainframe->triggerEvent('onAfterRoute');

	/**
	 * DISPATCH THE APPLICATION
	 *
	 * NOTE :
	 */
	$mainframe->dispatch();

	// trigger the onAfterDispatch events
	JDEBUG ? $_PROFILER->mark('afterDispatch') : null;
	$mainframe->triggerEvent('onAfterDispatch');

	/**
	 * RENDER  THE APPLICATION
	 *
	 * NOTE :
	 */
	$mainframe->render();

	// trigger the onAfterRender events
	JDEBUG ? $_PROFILER->mark('afterRender') : null;
	$mainframe->triggerEvent('onAfterRender');

	/**
	 * RETURN THE RESPONSE
	 */
	echo JResponse::toString($mainframe->getCfg('gzip'));
} catch (JException $e) {
	$e->set('level', E_ERROR);
	JError::throwError($e);
}
