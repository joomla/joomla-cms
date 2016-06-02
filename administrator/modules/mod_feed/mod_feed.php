<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the feed functions only once
require_once __DIR__ . '/helper.php';

$rssurl = $params->get('rssurl', '');
$rssrtl = $params->get('rssrtl', 0);

// Check if feed URL has been set
if (empty ($rssurl))
{
	echo '<div>';
	echo JText::_('MOD_FEED_ERR_NO_URL');
	echo '</div>';

	return;
}

$feed            = ModFeedHelper::getFeed($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_feed', $params->get('layout', 'default'));
