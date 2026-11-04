<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

if (empty($field->value) || empty($field->value['file'])) {
    return;
}

$displayData = [
    'src' => $field->value['file'],
];

if ($class = (string) $fieldParams->get('css_class', '')) {
    $displayData['class'] = $class;
}

if ($fieldParams->get('controls', 1) === 1) {
    $displayData['controls'] = 'controls';
}

if ($fieldParams->get('autoplay', 0) === 1) {
    $displayData['autoplay'] = 'autoplay';
}

if ($fieldParams->get('loop', 0) === 1) {
    $displayData['loop'] = 'loop';
}

if ($fieldParams->get('muted', 0) === 1) {
    $displayData['muted'] = 'muted';
}

$displayData['preload'] = $fieldParams->get('preload', 'auto');

echo LayoutHelper::render('joomla.html.audio', $displayData);
