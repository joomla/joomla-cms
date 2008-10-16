<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$rssurl	= $params->get('rssurl', '');
$rssrtl	= $params->get('rssrtl', 0);

//check if cache diretory is writable as cache files will be created for the feed
$cacheDir = JPATH_BASE.DS.'cache';
if (!is_writable($cacheDir))
{
	echo '<div>';
	echo JText::_('Please make cache directory writable.');
	echo '</div>';
	return;
}

//check if feed URL has been set
if (empty ($rssurl))
{
	echo '<div>';
	echo JText::_('No feed URL specified.');
	echo '</div>';
	return;
}

$feed = modFeedHelper::getFeed($params);
require(JModuleHelper::getLayoutPath('mod_feed'));
