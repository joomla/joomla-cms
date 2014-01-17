<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$headerText	= trim($params->get('header_text'));
$footerText	= trim($params->get('footer_text'));

require_once JPATH_ADMINISTRATOR . '/components/com_banners/helpers/banners.php';
BannersHelper::updateReset();
$list = &ModBannersHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_banners', $params->get('layout', 'default'));
