<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

if (empty($field->value) || empty($field->value['file'])) {
    return;
}

$displayData = [
    'src' => $field->value['file'],
];


if (!empty($class = (string) $fieldParams->get('css_class', ''))) {
    $displayData['class'] = $class;
}

if ((int) $fieldParams->get('video_poster', 1) && !empty($poster = $field->value['poster'])) {
    $displayData['poster'] = (HTMLHelper::cleanImageURL($poster))->url;
}

if ($fieldParams->get('controls', 1)) {
    $displayData['controls'] = 'controls';
}

if ($fieldParams->get('autoplay', 0)) {
    $displayData['autoplay'] = 'autoplay';
}

if ($fieldParams->get('loop', 0)) {
    $displayData['loop'] = 'loop';
}

if ($fieldParams->get('muted', 0)) {
    $displayData['muted'] = 'muted';
}

if ($fieldParams->get('video_playsinline', 1)) {
    $displayData['playsinline'] = 'playsinline';
}

$displayData['preload'] = $fieldParams->get('preload', 'auto');

echo LayoutHelper::render('joomla.html.video', $displayData);
