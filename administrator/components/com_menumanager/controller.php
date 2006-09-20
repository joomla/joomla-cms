<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.controller' );
require_once( JPATH_ADMINISTRATOR . '/components/com_menus/helper.php' );

/**
 * @package  Joomla
 * @subpackage Menus
 */
class MenuTypeController extends JController
{
	var $_option = 'com_menumanager';

	/**
	* Cancels an edit operation
	* @param option	options for the operation
	*/
	function cancel() {
		$this->setRedirect( 'index2.php?option=com_menumanager' );
	}
}
?>