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
use Joomla\Module\Banners\Site\Helper\ModBannersHelper;

$headerText = trim($params->get('header_text'));
$footerText = trim($params->get('footer_text'));

BannersHelper::updateReset();
$list = &ModBannersHelper::getList($params);

require ModuleHelper::getLayoutPath('mod_banners', $params->get('layout', 'default'));
