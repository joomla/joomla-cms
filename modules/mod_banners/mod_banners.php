<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Component\Banners\Administrator\Helper\BannersHelper;
use Joomla\Module\Banners\Site\Helper\ModBannersHelper;

$headerText = trim($params->get('header_text'));
$footerText = trim($params->get('footer_text'));

BannersHelper::updateReset();

$model = $app->bootComponent('com_banners')->createMVCFactory($app)->createModel('Banners', 'Site', ['ignore_request' => true]);
$list  = ModBannersHelper::getList($params, $model, $app);

require ModuleHelper::getLayoutPath('mod_banners', $params->get('layout', 'default'));
