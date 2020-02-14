<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

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
 */

/**
 * The format of the input tag to be filled in using sprintf.
 *     %1 - id
 *     %2 - name
 *     %3 - value
 *     %4 = any other attributes
 */
$standard 	= '<input type="checkbox" id="%1$s" value="%3$s=%2$s" %4$s /><label for="%1$s" class="checkbox" style="font-family:%2$s">%3$s</label>';
$custom 	= '<div class="span4 col-md-4"><input type="text" class="form-control span12" value="%3$s" placeholder="' . JText::_('WF_LABEL_NAME') . '" /></div><div class="span6 col-md-6"><input type="text" class="form-control span12" value="%2$s" placeholder="' . JText::_('WF_LABEL_FONTS') . ', eg: arial,helvetica,sans-serif" /></div><div class="span2 col-md-2"><a href="#" class="font-item-trash btn btn-link pull-right float-right"><i class="icon icon-trash"></i></a></div>';

// The alt option for JText::alt
$alt = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
?>

<fieldset id="<?php echo $id; ?>" class="<?php echo trim($class . ' checkboxes fontlist'); ?>">

	<?php foreach ($options as $i => $option): ?>
		<?php
			// Initialize some option attributes.
			$checked = $option->checked ? 'checked' : '';

			$optionDisabled = !empty($option->disable) || $disabled ? 'disabled' : '';

			$oid = $id . $i;
			$value = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
			$attributes = array_filter(array($checked, $optionDisabled));

			$format = $standard;

			if ($option->custom) {
				$format = $custom;
			}
		?>
        <div class="font-item row form-row" title="<?php echo $option->text; ?>">
            <?php echo sprintf($format, $oid, $value, $option->text, implode(' ', $attributes)); ?>
        </div>
	<?php endforeach;?>

	<div class="font-item row form-row controls controls-row">
        <?php echo sprintf($custom, '', '', '', ''); ?>
	</div>

	<div class="font-item row form-row" hidden>
        <?php echo sprintf($custom, '', '', '', ''); ?>
	</div>

	<a href="#" class="btn btn-link font-item-plus"><span class="span10 col-md-10 text-left"><?php echo JText::_('WF_PARAM_FONTS_NEW');?></span><i class="icon icon-plus pull-right float-right"></i></a>

	<input type="hidden" name="<?php echo $name;?>" value="" />

</fieldset>
