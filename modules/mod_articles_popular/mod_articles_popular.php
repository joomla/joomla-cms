<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the popular functions only once
JLoader::register('ModArticlesPopularHelper', __DIR__ . '/helper.php');

use Joomla\CMS\Helper\ModuleHelper;

$list = ModArticlesPopularHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require ModuleHelper::getLayoutPath('mod_articles_popular', $params->get('layout', 'default'));
