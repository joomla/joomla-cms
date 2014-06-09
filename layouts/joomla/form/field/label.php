<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * ------------------
 * 	- id       : (string) DOM id of the element
 * 	- element  : (SimpleXMLElement) The object of the <field /> XML element that describes the form field.
 * 	- field    : (JFormField) Object to access to the field properties
 * 	- multiple : (boolean) Allow to enter multiple values?
 * 	- name     : (string) Name of the field to display
 * 	- required : (boolean) Is this field required?
 * 	- value    : (mixed) Value of the field
 *
 */

$text = $element['label'] ? (string) $element['label'] : (string) $element['name'];
$text = $field->translateLabel ? JText::_($text) : $text;

$title = null;

if ($description = (string) $field->description)
{
	$description = $field->translateDescription ? JText::_($description) : $description;
	JHtml::_('bootstrap.tooltip');
	$title = JHtml::tooltipText(trim($text, ':'), $description, 0);
}

$classes = array();
$classes[] = !empty($description) ? 'hasTooltip' : null;
$classes[] = ($field->required == true) ? 'required' : null;
$classes[] = !empty($field->labelclass) ? (string) $field->labelclass : null;

?>
<label
	id="<?php echo $field->id; ?>-lbl"
	for="<?php echo $field->id; ?>"
	<?php if ($classes = array_filter($classes)) : ?>
		class="<?php echo implode(' ', $classes); ?>"
	<?php endif; ?>
	<?php if ($title) : ?>
		title="<?php echo $title; ?>"
	<?php endif; ?>
	>
		<?php echo $text; ?>
		<?php if ($field->required) : ?>
			<span class="star">&#160;*</span>
		<?php endif; ?>
</label>
