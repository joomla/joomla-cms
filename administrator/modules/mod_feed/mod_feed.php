<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$feed   = \Joomla\Module\Feed\Administrator\Helper\FeedHelper::getFeed($params);
$rssurl = $params->get('rssurl', '');
$rssrtl = $params->get('rssrtl', 0);

require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_feed', $params->get('layout', 'default'));
