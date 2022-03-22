<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the banners functions only once
JLoader::register('ModBannersHelper', __DIR__ . '/helper.php');

$headerText = trim($params->get('header_text', ''));
$footerText = trim($params->get('footer_text', ''));

JLoader::register('BannersHelper', JPATH_ADMINISTRATOR . '/components/com_banners/helpers/banners.php');
BannersHelper::updateReset();
$list = &ModBannersHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_banners', $params->get('layout', 'default'));
