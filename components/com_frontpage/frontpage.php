<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
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

define( 'JPATH_COM_FRONTPAGE', dirname( __FILE__ ));

$lang =& JFactory::getLanguage();
$lang->load('com_content');

// require the content helper
require_once (JApplicationHelper::getPath('helper', 'com_content'));

/**
 * Frontpage Component Controller
 *
 * @static
 * @package Joomla
 * @subpackage Frontpage
 * @since 1.5
 */
class FrontpageController
{
	function show()
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();
		$gid		= $user->get('gid');

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// Parameters
		$menus   =& JMenu::getInstance();
		$menu   =& $menus->getItem($Itemid);
		$params =& $menus->getParams($Itemid);

		require_once (dirname(__FILE__).DS.'models'.DS.'frontpage.php');
		$model = new ModelFrontpage( $params);

		// Dynamic Page Title
		$mainframe->SetPageTitle($menu->name);

		$doc  =& JFactory::getDocument();
		$function = 'show'.$doc->getType();
		
		require_once (JPATH_COM_FRONTPAGE.DS.'views'.DS.'blog'.DS.'blog.php');
		FrontpageView::$function($model, $access, $menu);
	}
}

/*
 * Show the frontpage
 */
FrontpageController::show();
?>