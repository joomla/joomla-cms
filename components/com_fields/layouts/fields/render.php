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

if (!$fields)
{
	return;
}

?>
<dl class="fields-container">
	<?php foreach ($fields as $field) : ?>
		<?php // If the value is empty do nothing ?>
		<?php if (!isset($field->value) || $field->value == '') : ?>
			<?php continue; ?>
		<?php endif; ?>
		<?php $class = $field->params->get('render_class'); ?>
		<dd class="field-entry <?php echo $class; ?>">
			<?php echo FieldsHelper::render($context, 'field.render', array('field' => $field)); ?>
		</dd>
	<?php endforeach; ?>
</dl>
