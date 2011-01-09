<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$rssurl	= $params->get('rssurl', '');
$rssrtl	= $params->get('rssrtl', 0);

//check if cache diretory is writable as cache files will be created for the feed
$cacheDir = JPATH_CACHE;
if (!is_writable($cacheDir))
{
	echo '<div>';
	echo JText::_('MOD_FEED_ERR_CACHE');
	echo '</div>';
	return;
}

//check if feed URL has been set
if (empty ($rssurl))
{
	echo '<div>';
	echo JText::_('MOD_FEED_ERR_NO_URL');
	echo '</div>';
	return;
}

require JModuleHelper::getLayoutPath('mod_feed');
