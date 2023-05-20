<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Feed\Site\Helper\FeedHelper;

$rssurl = $params->get('rssurl', '');
$rssrtl = $params->get('rssrtl', 0);

$feed = FeedHelper::getFeed($params);

require ModuleHelper::getLayoutPath('mod_feed', $params->get('layout', 'default'));
