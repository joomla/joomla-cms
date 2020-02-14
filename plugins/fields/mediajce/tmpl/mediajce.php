<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.MediaJce
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (C) 2019 Ryan Demmer. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.filesystem.path');

if ($field->value == '') {
    return;
}

$class = (string) $fieldParams->get('media_class', '');
$type = (string) $fieldParams->get('mediatype', 'images');
$text = (string) $fieldParams->get('media_description', '');

if ($class) {
    $class = ' class="' . htmlentities($class, ENT_COMPAT, 'UTF-8', true) . '"';
}

if ($text) {
    $text = htmlentities($text, ENT_COMPAT, 'UTF-8', true);
}

$value = (array) $field->value;
$buffer = '';

$element = '<img src="%s"%s alt="%s" />';

if ($type !== "images") {
    $element = '<a href="%s"%s>%s</a>';
}

foreach ($value as $path) {
    if (!$path) {
        continue;
    }

    // remove some common characters
    $path = preg_replace('#[\+\\\?\#%&<>"\'=\[\]\{\},;@\^\(\)£€$]#', '', $path);

    // trim
    $path = trim($path);

    // check for valid path after clean
    if (!$path) {
        continue;
    }

    // clean path
    $path = JPath::clean($path);

    // create full path
    $fullpath = JPATH_SITE . '/' . trim($path, '/');

    // check path is valid
    if (!is_file($fullpath)) {
        continue;
    }

    // set text as basename if not an image
    if (!$text && $type !== "images") {
        $text = basename($path);
    }

    $buffer .= sprintf($element,
        htmlentities($path, ENT_COMPAT, 'UTF-8', true),
        $class,
        $text
    );
}

echo $buffer;
