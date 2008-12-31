<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Search
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once JPATH_COMPONENT.DS.'controller.php';

$controller = new SearchController();
$controller->execute( JRequest::getCmd( 'task' ) );
$controller->redirect();