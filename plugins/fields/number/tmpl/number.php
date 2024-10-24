<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Number
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$value = $field->value;

if (is_numeric($value)) {
    $value = (float) $value;
} else {
    $value = '';
    $value = isset($min) ? $min : $value;
}

echo $value;
