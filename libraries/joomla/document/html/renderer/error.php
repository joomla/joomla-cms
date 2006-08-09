<?php
/**
 * @version $Id: head.php 4330 2006-07-26 06:24:14Z webImagery $
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * JDocument error renderer
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentRenderer_Error extends JDocumentRenderer
{
	/**
	 * Renders the error stack and returns the results as a string
	 *
	 * @access public
	 * @param string 	$name		(unused)
	 * @param array 	$params		Associative array of values
	 * @return string	The output of the script
	 */
	function render($name = null, $params = array ())
	{
		// Initialize variables
		$contents = null;

		// Get the error queue
		$errors = JError::getErrors();

		// If there is a persisted error queue, merge it with our existing error queue
		$oldErrors = JSession::get('_JError_queue');
		if (is_array($oldErrors)) {
			// Import library dependencies
			jimport('pattemplate.patError');
			$oe = array();
			foreach ($oldErrors as $e)
			{
				$oe[] = new patError($e['level'], $e['code'], $e['message'], $e['info']);
			}
			$errors = array_merge($oe, $errors);
			JSession::set('_JError_queue', null);
		}

		// If errors exist render them
		if (count($errors)) {
			// Build the system error div
			$contents .= "\n<div id=\"system-error\">" .
					"\n<ul>";
			foreach ($errors as $error)
			{
				$contents .= "\n<li>".$error->getMessage()."</li>";
			}
			$contents .= "\n</ul>" .
					"\n</div>";
		}
		return $contents;
	}
}
?>