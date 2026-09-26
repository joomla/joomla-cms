<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Layout variables
 * -----------------
 * @var   array  $displayData  Array with all the given attributes for the image element.
 *                             Eg: src, class, alt, width, height, loading, decoding, style, data-*
 *                             Note: only the alt and src attributes are escaped by default!
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

$img = HTMLHelper::_('cleanImageURL', $displayData['src']);

// Prepare attributes
$displayData['src'] = $img->url;

if (isset($displayData['alt']) && $displayData['alt'] === false) {
    unset($displayData['alt']);
}

if ($img->attributes['width'] > 0 && $img->attributes['height'] > 0) {
    $displayData['width']  = $img->attributes['width'];
    $displayData['height'] = $img->attributes['height'];

    if (empty($displayData['loading'])) {
        $displayData['loading'] = 'lazy';
    }
}

// Escape attributes before output
$attributes = [];

foreach ($displayData as $attributeName => $attributeValue) {
    // Skip invalid attribute names
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_:.-]*$/', $attributeName)) {
        continue;
    }

    $attributes[$attributeName] = htmlspecialchars((string) $attributeValue, ENT_QUOTES, 'UTF-8');
}

echo '<img ' . ArrayHelper::toString($attributes) . '>';
