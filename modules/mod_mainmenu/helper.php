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
defined('_JEXEC') or die('Restricted access');

class modMainMenuHelper
{
	function render(&$params)
	{
		switch ( $params->get( 'menu_style', 'list' ) )
		{
			case 'list_flat' :
				// Include the legacy library file
				require_once(dirname(__FILE__).DS.'legacy.php');
				mosShowHFMenu($params, 1);
				break;

			case 'horiz_flat' :
				// Include the legacy library file
				require_once(dirname(__FILE__).DS.'legacy.php');
				mosShowHFMenu($params, 0);
				break;

			case 'vert_indent' :
				// Include the legacy library file
				require_once(dirname(__FILE__).DS.'legacy.php');
				mosShowVIMenu($params);
				break;

			default :
				// Include the new menu class
				require_once(dirname(__FILE__).DS.'menu.php');

				// Initialize some variables
				$Itemid = JRequest::getVar( 'Itemid', 0, '', 'int' );
				$menu = new JMainMenu($params, $Itemid);
				$items = &JMenu::getInstance();

				// Get Menu Items
				$rows = $items->getItems('menutype', $params->get('menutype'));

				// Build Menu Tree
				$user =& JFactory::getUser();
				foreach ($rows as $row) {
					if($row->access <= $user->get('gid')) {
						$menu->addNode($row);
					}
				}

				// Render Menu
				$menu->render($params->get('menutype'), $params->get('class_sfx'));
				break;
		}
	}
}
