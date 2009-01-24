<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Get the component title div
$app	= &JFactory::getApplication();
$title	= $app->get('JComponentTitle');

// Echo title if it exists
if (!empty($title)) {
	echo $title;
}