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
		
		<?php $showLabel = $field->params->get('showlabel'); ?>
		<?php $class = $field->params->get('render_class'); ?>
		<?php $label = JText::_($field->label); ?>
		<?php $value = $field->value; ?>
		
		<dt class="field-label <?php echo $class; ?>">
		<?php if ($showLabel == 1) : ?>
			<?php echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>
		<?php endif; ?>
		</dt>
		<dd class="field-value <?php echo $class; ?>"><?php echo $value; ?></dd>
	<?php endforeach; ?>
</dl>
