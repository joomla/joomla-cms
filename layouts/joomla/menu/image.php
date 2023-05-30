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
    'src' => $displayData->menu_image,
    'alt' => '',
];

if ($displayData->menu_image_css)
{
    $attributes['class'] = $displayData->menu_image_css;
}

echo LayoutHelper::render('joomla.html.image', $attributes);
