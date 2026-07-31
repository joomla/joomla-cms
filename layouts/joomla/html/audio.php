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

if (array_key_exists('src', $displayData) && !empty($displayData['src'])) {
    $displayData['src'] = $this->escape($displayData['src']);
}

echo '<audio ' . ArrayHelper::toString($displayData) . '></audio>';
