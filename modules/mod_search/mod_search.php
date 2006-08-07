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

if (!defined( 'JPATH_MOD_SEARCH' ))
{
	define( 'JPATH_MOD_SEARCH', dirname(__FILE__) );
}

// Include the syndicate functions only once
require_once( JPATH_MOD_SEARCH.DS.'helper.php' );

$inputfield = modSearchHelper::renderInputField($params);
$itemid     = modSearchHelper::getItemid($params);

$template	= $params->get( 'template_name' );
$template	= preg_replace( '#\W#', '', $template );

if (!file_exists( JPATH_MOD_SEARCH . '/templates/' . $template . '.html' ))
{
	$template = 'module';
}
// TODO: We could look in a template folder as well for a variation??
require( JPATH_MOD_SEARCH.'/templates/' . $template . '.html' );
