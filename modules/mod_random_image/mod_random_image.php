<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$link 	 = $params->get( 'link' );

$folder	= modRandomImageHelper::getFolder($params);
$images	= modRandomImageHelper::getImages($params, $folder);

if (!count($images)) {
	echo JText::_( 'No images ');
	return;
}

$image = modRandomImageHelper::getRandomImage($params, $images);
require(JModuleHelper::getLayoutPath('mod_random_image'));
