<?php
/**
* @version $Id: mod_rssfeed.php 588 2005-10-23 15:20:09Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport( 'joomla.application.controller' );
/*
 * Include the syndicate functions only once
 */
require_once (dirname(__FILE__).DS.'syndicate.functions.php');

$controller = new JModSyndicateController( $mainframe );
$controller->params = &$params;
$controller->execute( 'display' );
?>