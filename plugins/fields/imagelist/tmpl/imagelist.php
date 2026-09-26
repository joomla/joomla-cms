<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Imagelist
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Image\Image;

if ($field->value == '') {
    return;
}

$class = $fieldParams->get('image_class');

if ($class) {
    // space before, so if no class sprintf below works
    $class = ' class="' . htmlentities($class, ENT_COMPAT, 'UTF-8', true) . '"';
}

$value  = (array) $field->value;
$buffer = '';

foreach ($value as $path) {
    if (!$path || $path == '-1') {
        continue;
    }

    $imageFilePath = htmlentities($path, ENT_COMPAT, 'UTF-8', true);

    if ($fieldParams->get('directory', '/') !== '/') {
        $imageInfo = Image::getImageFileProperties(JPATH_ROOT . '/images/' . $fieldParams->get('directory') . '/' . $imageFilePath);

        $buffer .= sprintf(
            '<img loading="lazy" width="%s" height="%s" src="images/%s/%s"%s alt="">',
            $imageInfo->width,
            $imageInfo->height,
            $fieldParams->get('directory'),
            $imageFilePath,
            $class
        );
    } else {
        $imageInfo = Image::getImageFileProperties(JPATH_ROOT . '/images/' . $imageFilePath);

        $buffer .= sprintf(
            '<img loading="lazy" width="%s" height="%s" src="images/%s"%s>',
            $imageInfo->width,
            $imageInfo->height,
            $imageFilePath,
            $class
        );
    }
}

echo $buffer;
