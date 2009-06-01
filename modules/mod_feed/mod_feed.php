<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_feed
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

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
require JModuleHelper::getLayoutPath('mod_feed');
