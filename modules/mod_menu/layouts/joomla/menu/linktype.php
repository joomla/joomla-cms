<?php

/**
 * @package         Joomla.Site
 * @subpackage      Layout
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if ($displayData['menu_icon'])
{
    echo $this->sublayout('icon', ['icon' => $displayData['menu_icon']]);
}
elseif ($displayData['menu_image'])
{
    echo $this->sublayout('image', ['src' => $displayData['menu_image'], 'class' => $displayData['menu_image_css']]);
}

$wrapperStart = $displayData['menu_image'] ? '<span class="image-title">' : '';
$wrapperEnd   = $displayData['menu_image'] ? '</span>' : '';

// Display / Hide Menu Item Title
if ($displayData['menu_text'])
{
    echo $wrapperStart;
    echo $displayData['title'];
    echo $wrapperEnd;
} else {
    echo '<span class="visually-hidden">' . $displayData['title'] . '</span>';
}
