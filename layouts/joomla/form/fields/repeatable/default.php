<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 * 	$id         : (string) id of the element
 * 	$name       : (string) name of the element
 * 	$value      : (mixed) current value of the element, may be an array?
 * 	$form       : (JForm) the form that this field belongs to
 * 	$subForm    : (JForm) the subform that serves as the basis for the modal content
 * 	$element    : (SimpleXMLElement) the xml element for this field
 * 	$attributes : (SimpleXMLElement) the attributes of the xml element
 * 	$maximum    : (integer) maximum number of rows allowed
 */

$id         = $displayData->id;
$name       = $displayData->name;
$value      = $displayData->value;
$form       = $displayData->form;
$subForm    = $displayData->subForm;
$element    = $displayData->element;
$attributes = $displayData->attributes;
$maximum    = $displayData->maximum;

$modalid = $id . '_modal';
$class = (string) $element['class'];
$names = array();

$select = $element['select'] ? JText::_((string) $element['select']) : JText::_('JLIB_FORM_BUTTON_SELECT');
$icon   = $element['icon'] ? '<i class="icon-' . (string) $element['icon'] . '"></i> ' : '';

if (is_array($value))
{
	$value = array_shift($value);
}

if (!isset($value) || is_null($value) || $value == 'null')
{
	$value = '{}';
}

JHtml::_('script', 'system/repeatable.js', true, true);

JText::script('JAPPLY');
JText::script('JCANCEL');

?>

<div id="<?php echo $modalid; ?>" style="display:none">
	<table id="<?php echo $modalid; ?>_table" class="adminlist table table-striped <?php echo $class; ?>">
		<thead>
			<tr>
				<?php foreach ($subForm->getFieldset($attributes->name . '_modal') as $field) : ?>
					<?php $names[] = (string) $field->getAttribute('name'); ?>
					<th>
						<?php echo strip_tags($field->label); ?>
						<br />
						<small style="font-weight:normal"><?php echo JText::_($field->description); ?></small>
					</th>
				<?php endforeach; ?>

				<th><a href="#" class="add btn button btn-success"><span class="icon-plus"></span> </a></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<?php foreach ($subForm->getFieldset($attributes->name . '_modal') as $field) : ?>
					<td><?php echo $field->input; ?></td>
				<?php endforeach; ?>

				<td>
					<div class="btn-group"><a class="add btn button btn-success"><span class="icon-plus"></span> </a>
					<a class="remove btn button btn-danger"><span class="icon-minus"></span> </a></div>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<button class="btn" id="<?php echo $modalid; ?>_button" data-modal="<?php echo $modalid; ?>"><?php echo $icon, $select; ?></button>

<input type="hidden" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" />

<?php

JFactory::getDocument()->addScriptDeclaration('
	jQuery(function ($) {
			new $.JRepeatable(' . json_encode($id) . ', ' . json_encode($names) . ', ' . (int) $maximum . ');
	});
');
