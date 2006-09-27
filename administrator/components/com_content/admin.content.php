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
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_COMPONENT.DS.'controller.php' );
require_once( JPATH_COMPONENT.DS.'helper.php' );
require_once (JApplicationHelper::getPath('admin_html'));

$controller = new JContentController();
$controller->setModelPath( JPATH_COMPONENT.DS.'models'.DS );
$controller->setViewPath( JPATH_COMPONENT.DS.'views'.DS );

switch (strtolower($task))
{
	case 'element':
	case 'wizard':
		$controller->execute( $task );
		$controller->redirect();
		break;

	case 'add'  : 
	case 'edit' :
		JContentController::editContent();
		break;

	case 'go2menu' :
	case 'go2menuitem' :
	case 'resethits' :
	case 'menulink' :
	case 'apply' :
	case 'save' :
		JContentController::saveContent();
		break;

	case 'remove' :
		JContentController::removeContent();
		break;

	case 'publish' :
		JContentController::changeContent(1);
		break;

	case 'unpublish' :
		JContentController::changeContent(0);
		break;

	case 'toggle_frontpage' :
		JContentController::toggleFrontPage();
		break;

	case 'archive' :
		JContentController::changeContent(-1);
		break;

	case 'unarchive' :
		JContentController::changeContent(0);
		break;

	case 'cancel' :
		JContentController::cancelContent();
		break;

	case 'orderup' :
		JContentController::orderContent(-1);
		break;

	case 'orderdown' :
		JContentController::orderContent(1);
		break;

	//case 'showarchive' :
	//	JContentController::viewArchive();
	//	break;

	case 'movesect' :
		JContentController::moveSection();
		break;

	case 'movesectsave' :
		JContentController::moveSectionSave();
		break;

	case 'copy' :
		JContentController::copyItem();
		break;

	case 'copysave' :
		JContentController::copyItemSave();
		break;

	case 'accesspublic' :
		JContentController::accessMenu(0);
		break;

	case 'accessregistered' :
		JContentController::accessMenu(1);
		break;

	case 'accessspecial' :
		JContentController::accessMenu(2);
		break;

	case 'saveorder' :
		JContentController::saveOrder();
		break;

	case 'preview' :
		JContentController::previewContent();
		break;

	default :
		JContentController::viewContent();
		break;
}

?>