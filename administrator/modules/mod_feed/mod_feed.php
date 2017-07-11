<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the feed functions only once
JLoader::register('ModFeedHelper', __DIR__ . '/helper.php');

$feed            = ModFeedHelper::getFeed($params);
$rssurl          = $params->get('rssurl', '');
$rssrtl          = $params->get('rssrtl', 0);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_feed', $params->get('layout', 'default'));
