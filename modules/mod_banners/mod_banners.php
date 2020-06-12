<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Component\Banners\Administrator\Helper\BannersHelper as BannersComponentHelper;
use Joomla\Module\Banners\Site\Helper\BannersHelper;

$headerText = trim($params->get('header_text'));
$footerText = trim($params->get('footer_text'));

BannersComponentHelper::updateReset();

$model = $app->bootComponent('com_banners')->getMVCFactory()->createModel('Banners', 'Site', ['ignore_request' => true]);
$list  = BannersHelper::getList($params, $model, $app);

require ModuleHelper::getLayoutPath('mod_banners', $params->get('layout', 'default'));
