<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class UserViewUser extends JView
{
	function display( $tpl = null)
	{
		global $mainframe, $Itemid;

		$pathway =& $mainframe->getPathWay();

		// Get the paramaters of the active menu item
		$menus	= &JMenu::getInstance();
		$menu	= $menus->getItem($Itemid);

		// Add breadcrumb
		$pathway->setItemName(1, 'User');
		$pathway->addItem( $menu->name, '' );

		parent::display($tpl);
	}
}
?>