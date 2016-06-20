<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$params->def('format', 'rss');

$link = ModSyndicateHelper::getLink($params);

if (is_null($link))
{
	return;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');
$text            = htmlspecialchars($params->get('text'), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_syndicate', $params->get('layout', 'default'));
