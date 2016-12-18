<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 *
 * @var   string   $asset           The asset text
 * @var   string   $authorField     The label text
 * @var   string   $link            The link text
 */
extract($displayData);

// Load the modal behavior script.
JHtml::_('behavior.modal');

// Include jQuery
JHtml::_('jquery.framework');

// Initialize some field attributes.
$attr = !empty($class) ? ' class="' . $class . '"' : '';
$attr .= !empty($size) ? ' size="' . $size . '"' : '';

// The hidden text field.
$jsonvalue = $value ? htmlspecialchars($value, ENT_COMPAT, 'UTF-8') : '';
$linkoriginal = ($link ? $link : 'index.php?option=com_content&amp;view=link&amp;tmpl=component&amp;asset=' . $asset . '&amp;author=' . $authorField);
?>
<div class="input-prepend input-append">
	<input type="hidden" name="<?php echo $name . '" id="' . $id . '" value="' . $jsonvalue . '" readonly="readonly"' . $attr; ?> />
	<a class="modal btn" title="<?php echo JText::_('JLIB_FORM_BUTTON_SELECT'); ?>" id="<?php echo $id; ?>-btn" href="
	<?php echo ($readonly ? '' : $linkoriginal . '&amp;fieldid=' . $id . '&amp;link=' . $jsonvalue) . '"'
		. ' rel="{handler: \'iframe\', size: {x: 800, y: 260}}"'; ?>><?php echo JText::_('JLIB_FORM_BUTTON_SELECT'); ?></a>
	<a class="btn hasTooltip" title="<?php echo JText::_('JLIB_FORM_BUTTON_CLEAR'); ?>" href="#" onclick="jInsertFieldValue('', '<?php echo $id; ?>');var btn = document.getElementById('<?php echo $id; ?>-btn');btn.href = '<?php echo $linkoriginal . '&amp;fieldid=' . $id . "&amp;link="; ?>'; return false;">
		<i class="icon-remove"></i>
	</a>
</div>
