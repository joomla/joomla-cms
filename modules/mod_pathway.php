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

// Get the pathway object from the application
$pathway = $mainframe->getPathWay();

$viewComponent = true;

// Don't show content component in the pathway when viewing content
if ($mainframe->getOption() == 'com_content') {
	$viewComponent = false;
}

// Print pathway in XHTML format
echo $pathway->toXHTML(true, $viewComponent);

/*
 * Jinx, this is the patT version

//$pathway->addItem( 'Test1', '' );
//$pathway->addItem( 'Test2', 'dfdf' );

$tmpl = &JFactory::getPatTemplate( array( 'pathway.html' ) );
$tmpl->addVar( 'pathway-items', 'separator', $pathway->_separator );  // $pathway->getSeparator() would be good
$tmpl->addObject( 'pathway-items', $pathway->_pathway ); // $pathway->getPathway() would be good here
$tmpl->displayParsedTemplate( 'pathway' );
*/
?>