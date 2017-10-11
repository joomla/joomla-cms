<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Breadcrumbs\Site\Helper\BreadcrumbsHelper;

// Get the breadcrumbs
$list  = BreadcrumbsHelper::getList($params);
$count = count($list);

require ModuleHelper::getLayoutPath('mod_breadcrumbs', $params->get('layout', 'default'));
