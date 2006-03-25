<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Include the breadcrumbs functions only once
 */
require_once (dirname(__FILE__).DS.'breadcrumbs.functions.php');

/*
 * Initialize variables
 */
$showHome = true;
$showComponent = true;

// Set the default separator
$separator = setSeparator();

/*
 * Perhaps we should have a parameter to control whether home is displayed in the 
 * pathway or not.
 */
if ($params->get('showHome') == false)
{
	$showHome = false;
}

/*
 * Do not show the content component item in the BreadCrumbs list
 */
if (JRequest::getVar('option') == 'com_content')
{
	$showComponent = false;
}

// Get the PathWay object from the application
$pathway = & $mainframe->getPathWay();
$crumbs = $pathway->getPathWay($showHome, $showComponent);

/*
 * This is the JTemplate method of displaying the BreadCrumbs for maximum flexibility
 */
//$tmpl = & JTemplate::getInstance();
//$tmpl->parse( 'breadcrumbs.html' );

//$tmpl->addVar   ( 'breadcrumbs-items', 'separator', $separator );
//$tmpl->addObject( 'breadcrumbs-items', $crumbs );

//$tmpl->display( 'breadcrumbs' );

/*
 * This is the standard way of displaying the BreadCrumbs
 */
echo showBreadCrumbs($crumbs, $separator);
?>