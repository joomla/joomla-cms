<?php
/**
 * @version		$Id: mod_feed.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

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

require(JModuleHelper::getLayoutPath('mod_feed'));
