<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Component\Banners\Administrator\Helper\BannersHelper;

// Include the banners functions only once
JLoader::registerNamespace('Joomla\\Component\\Banners\\Administrator', JPATH_ADMINISTRATOR . '/components/com_banners' , false, false, 'psr4');
JLoader::registerNamespace('Joomla\\Component\\Banners\\Site', JPATH_ROOT . '/components/com_banners' , false, false, 'psr4');
JLoader::register('ModBannersHelper', __DIR__ . '/helper.php');

$headerText = trim($params->get('header_text'));
$footerText = trim($params->get('footer_text'));

BannersHelper::updateReset();
$list = &ModBannersHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require ModuleHelper::getLayoutPath('mod_banners', $params->get('layout', 'default'));
