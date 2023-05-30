<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

$attributes = [
    'icon'   => $displayData->menu_icon,
    'suffix' => 'p-2',
];

echo LayoutHelper::render('joomla.icon.iconclass', $attributes);
