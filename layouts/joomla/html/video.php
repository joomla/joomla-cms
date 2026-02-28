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

if (array_key_exists('src', $displayData) && !empty($displayData['src'])) {
    $displayData['src'] = $this->escape($displayData['src']);
}

$source = [];
if (isset($displayData['source']) && is_array($displayData['source'])) {
    $source = $displayData['source'];
    unset($displayData['source']);
}

echo '<video ' . ArrayHelper::toString($displayData) . '>';
if (!empty($source)) {
    foreach ($source as $sourceData) {
        echo '<source src="' . $this->escape($sourceData['src']) . '" type="' . $sourceData['type'] . '" />';
    }
}
echo '</video>';
