<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_wrapper
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Wrapper\Site\Helper\WrapperHelper;

$params = WrapperHelper::getParams($params);

$load        = $params->get('load');
$url         = htmlspecialchars($params->get('url', ''), ENT_COMPAT, 'UTF-8');
$target      = htmlspecialchars($params->get('target', ''), ENT_COMPAT, 'UTF-8');
$width       = htmlspecialchars($params->get('width', ''), ENT_COMPAT, 'UTF-8');
$height      = htmlspecialchars($params->get('height', ''), ENT_COMPAT, 'UTF-8');
$ititle      = $module->title;
$id          = $module->id;
$lazyloading = $params->get('lazyloading', 'lazy');

require ModuleHelper::getLayoutPath('mod_wrapper', $params->get('layout', 'default'));
