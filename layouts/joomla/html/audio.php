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
 * @var   array $displayData   Array with all the given attributes for the audio element.
 *                             Eg: src, class, controls, controlslist, autoplay, loop, style, data-*
 *                             See: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/audio
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

$attributes = [];

foreach ($displayData as $attributeName => $attributeValue) {
    // Skip invalid attribute names
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_:.-]*$/', $attributeName)) {
        continue;
    }

    $attributes[$attributeName] = htmlspecialchars((string) $attributeValue, ENT_QUOTES, 'UTF-8');
}

echo '<audio ' . ArrayHelper::toString($attributes) . '></audio>';
