<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Check if we have all the data
if (!key_exists('item', $displayData) || !key_exists('context', $displayData))
{
	return;
}

// Setting up for display
$item = $displayData['item'];

if (!$item)
{
	return;
}

$context = $displayData['context'];

if (!$context)
{
	return;
}

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

$parts = explode('.', $context);
$component = $parts[0];
$fields = null;

if (key_exists('fields', $displayData))
{
	$fields = $displayData['fields'];
}
else
{
	$fields = $item->fields ?: FieldsHelper::getFields($context, $item, true);
}

if (!$fields)
{
	return;
}

// Print the container tag
echo '<dl class="fields-container">';

// Loop through the fields and print them
foreach ($fields as $field)
{
	// If the value is empty do nothing
	if (!isset($field->value) || $field->value == '')
	{
		continue;
	}

	echo FieldsHelper::render($context, 'field.render', array('field' => $field));
}

// Close the container
echo '</dl>';

echo "<br/><strong>com_fields template override of layout</strong><br/>";
