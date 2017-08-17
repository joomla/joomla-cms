<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Syndicate\Site\Helper\SyndicateHelper;

$params->def('format', 'rss');

$link = SyndicateHelper::getLink($params);

if ($link === null)
{
	return;
}

$text            = htmlspecialchars($params->get('text'), ENT_COMPAT, 'UTF-8');

require ModuleHelper::getLayoutPath('mod_syndicate', $params->get('layout', 'default'));
