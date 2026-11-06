<?php

/**
 * @package         Joomla.Site
 * @subpackage      Layout
 *
 * @copyright       (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Layout variables
 * -----------------
 * @var   array $displayData   Array with all the given attributes for the video element.
 *                             Eg: src, class, controls, width, height, autoplay, decoding, style, data-*
 *                             Note: <source> tags for <video> - is array of arrays with `src` and `type` keys.
 *                             $displayData['source'] = [ ['src' => 'path/to/video.mp4', 'type' => 'video/mp4'] ];
 *                             See: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/video
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

$source = [];
if (isset($displayData['source']) && is_array($displayData['source'])) {
    $source = $displayData['source'];
    unset($displayData['source']);
}

$attributes = [];

foreach ($displayData as $attributeName => $attributeValue) {
    // Skip invalid attribute names
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_:.-]*$/', $attributeName)) {
        continue;
    }

    $attributes[$attributeName] = htmlspecialchars((string) $attributeValue, ENT_QUOTES, 'UTF-8');
}

echo '<video ' . ArrayHelper::toString($attributes) . '>';
if (!empty($source)) {
    foreach ($source as $sourceData) {
        echo '<source src="' . $this->escape($sourceData['src']) . '" type="' . $this->escape($sourceData['type']) . '" />';
    }
}
echo '</video>';
