<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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

$parts     = explode('.', $context);
$component = $parts[0];
$fields    = null;

if (key_exists('fields', $displayData))
{
	$fields = $displayData['fields'];
}
else
{
	$fields = $item->jcfields ?: FieldsHelper::getFields($context, $item, true);
}

if (empty($fields))
{
	return;
}

$output = array();

foreach ($fields as $field)
{
	// If the value is empty do nothing
	if (!isset($field->value) || $field->value == '')
	{
		continue;
	}

	$class = $field->params->get('render_class');
	$layout = $field->params->get('layout', 'render');
	$content = FieldsHelper::render($context, 'field.' . $layout, array('field' => $field));

	$output[] = '<dd class="field-entry ' . $class . '">' . $content . '</dd>';
}

if (empty($output))
{
	return;
}

?>
<dl class="fields-container">
	<?php echo implode("\n", $output); ?>
</dl>
