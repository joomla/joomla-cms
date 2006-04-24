<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Frontpage component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.0
 */
class JViewFrontpage
{

	function show(&$model, &$access, &$menu)
	{
		$format = JRequest::getVar( 'format', 'html' );

		require_once (dirname(__FILE__).DS.'view'.DS.'blog'.DS.'blog.'.$format.'.php');

		/*
		 * Need to cache this for speed
		 */
		JViewBlog::show($model, $access, $menu);
	}

	/**
	 * Method to show an empty container if there is no data to display
	 *
	 * @static
	 * @param string $msg The message to show
	 * @return void
	 * @since 1.5
	 */
	function emptyContainer($msg) {
		echo '<p><div align="center">'.$msg.'</div></p>';
	}

	/**
	 * Writes a user input error message and if javascript is enabled goes back
	 * to the previous screen to try again.
	 *
	 * @param string $msg The error message to display
	 * @return void
	 * @since 1.5
	 */
	function userInputError($msg) {
		josErrorAlert($msg);
	}
}
?>