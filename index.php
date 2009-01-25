<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Set flag that this is a parent file.
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

try {
	require_once JPATH_BASE.DS.'includes'.DS.'defines.php';
	require_once JPATH_BASE.DS.'includes'.DS.'framework.php';

	// Mark afterLoad in the profiler.
	JDEBUG ? $_PROFILER->mark('afterLoad') : null;

	/*
	 * Instantiate the application.
	 */
	$mainframe =& JFactory::getApplication('site');

	/*
	 * Initialise the application.
	 */
	$mainframe->initialise();

	// Mark afterIntialise in the profiler.
	JDEBUG ? $_PROFILER->mark('afterInitialise') : null;

	/*
	 * Route the application.
	 */
	$mainframe->route();

	// Mark afterRoute in the profiler.
	JDEBUG ? $_PROFILER->mark('afterRoute') : null;

	/*
	 * Dispatch the application.
	 */
	$mainframe->dispatch();

	// Mark afterDispatch in the profiler.
	JDEBUG ? $_PROFILER->mark('afterDispatch') : null;

	/*
	 * Render the application.
	 */
	$mainframe->render();

	// Mark afterRender in the profiler.
	JDEBUG ? $_PROFILER->mark('afterRender') : null;

	/*
	 * Return the response.
	 */
	echo JResponse::toString($mainframe->getCfg('gzip'));
}
catch (JException $e) {
	$e->set('level', E_ERROR);
	JError::throwError($e);
}