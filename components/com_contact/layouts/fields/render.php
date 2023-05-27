<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// Check if we have all the data
if (!array_key_exists('item', $displayData) || !array_key_exists('context', $displayData)) {
    return;
}

// Setting up for display
$item = $displayData['item'];

if (!$item) {
    return;
}

$context = $displayData['context'];

if (!$context) {
    return;
}

$parts     = explode('.', $context);
$component = $parts[0];
$fields    = null;

if (array_key_exists('fields', $displayData)) {
    $fields = $displayData['fields'];
} else {
    $fields = $item->jcfields ?: FieldsHelper::getFields($context, $item, true);
}

// Do nothing when not in mail context, like that the default rendering is used
if (!$fields || reset($fields)->context !== 'com_contact.mail') {
    return;
}

// Loop through the fields and print them
foreach ($fields as $field) {
    // If the value is empty do nothing
    if (!strlen($field->value)) {
        continue;
    }

    $layout = $field->params->get('layout', 'render');
    echo FieldsHelper::render($context, 'field.' . $layout, ['field' => $field]);
}
