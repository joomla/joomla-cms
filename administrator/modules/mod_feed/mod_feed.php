<?php

use Joomla\Module\Feed\Administrator\Helper\FeedHelper;
use Joomla\CMS\Helper\ModuleHelper;
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$feed   = FeedHelper::getFeed($params);
$rssurl = $params->get('rssurl', '');
$rssrtl = $params->get('rssrtl', 0);

require ModuleHelper::getLayoutPath('mod_feed', $params->get('layout', 'default'));
