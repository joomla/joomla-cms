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

// require the content helper
require_once (JPATH_SITE . '/components/com_content/content.helper.php');

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ( JRequest::getVar( 'task' ) )
{
	default:
		FrontpageController::display();
		break;
}

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
	function display()
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();
		$doc  	=& JFactory::getDocument();
		$gid	= $user->get('gid');

		// get request variables
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// Parameters
		$menus   =& JMenu::getInstance();
		$menu   =& $menus->getItem($Itemid);
		$params =& $menus->getParams($Itemid);

		require_once (JPATH_COM_FRONTPAGE.DS.'models'.DS.'frontpage.php');
		$model = new ModelFrontpage($params);

		// Dynamic Page Title
		$mainframe->SetPageTitle($menu->name);

		require_once (JPATH_COM_FRONTPAGE.DS.'views'.DS.'frontpage'.DS.'view.php');
		$view = new FrontpageViewFrontpage();

		$view->setModel($model, true);

		$request = new stdClass();
		$request->limitstart = $limitstart;

		$data = new stdClass();
		$data->error  = null;
		$data->access = $access;

		$view->set('data'   , $data);
		$view->set('params' , $params);
		$view->set('request', $request);
		$view->display();
	}
}
?>