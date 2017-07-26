<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$feed            = \Joomla\Module\Feed\Administrator\Helper\FeedHelper::getFeed($params);
$rssurl          = $params->get('rssurl', '');
$rssrtl          = $params->get('rssrtl', 0);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_feed', $params->get('layout', 'default'));
