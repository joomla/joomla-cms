<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$params->def('text', 'Feed Entries');
$params->def('format', 'rss');

$link = modSyndicateHelper::getLink($params);

if(is_null($link)) {
	return;
}

$img = JHtml::_('image.site', 'livemarks.png', '/images/M_images/');
require(JModuleHelper::getLayoutPath('mod_syndicate'));
