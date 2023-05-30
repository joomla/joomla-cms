<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Display Icon if set
echo $displayData['icon'] ?? null;

// Display Image if set
echo $displayData['image'] ?? null;

// Prepare Linktype wrapper. Only used when image is set.
$wrapperStart = isset($displayData['image']) ? '<span class="image-title">' : '';
$wrapperEnd   = isset($displayData['image']) ? '</span>' : '';

// Display / Hide Menu Item Title
if ($displayData['menu_text'])
{
    echo $wrapperStart;
    echo $displayData['title'];
    echo $wrapperEnd;
} else {
    echo '<span class="visually-hidden">' . $displayData['title'] . '</span>';
}
