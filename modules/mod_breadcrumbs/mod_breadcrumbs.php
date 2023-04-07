<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Breadcrumbs\Site\Helper\BreadcrumbsHelper;

// Get the breadcrumbs
$list  = BreadcrumbsHelper::getList($params, $app);
$count = count($list);

// Get the home fallback for json+ld
if (!$params->get('showHome', 1)) {
    $homeCrumb = BreadcrumbsHelper::getHome($params, $app);
}

require ModuleHelper::getLayoutPath('mod_breadcrumbs', $params->get('layout', 'default'));
