<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData, null);

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
 * @var   array    $options         Options available for this field.
 */

$alt         = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
$isBtnGroup  = strpos(trim($class), 'btn-group') !== false;
$isBtnYesNo  = strpos(trim($class), 'btn-group-yesno') !== false;
$dataToggle  = $isBtnGroup ? ' data-toggle="buttons"' : '';
$classToggle = $isBtnGroup ? ' btn-group-toggle' : '';
$btnClass    = $isBtnGroup ? 'btn btn-outline-secondary' : 'form-check';

// Add the attributes of the fieldset in an array
$attribs = ['class="' . trim(
		$class . ' radio' . ($readonly || $disabled ? ' disabled' : '') . ($readonly ? ' readonly' : '')
	) . $classToggle . '"',];

if (!empty($disabled))
{
	$attribs[] = 'disabled';
}

if (!empty($required))
{
	$attribs[] = 'required';
}

if (!empty($autofocus))
{
	$attribs[] = 'autofocus';
}

if (!empty($dataToggle))
{
	$attribs[] = $dataToggle;
}

if ($readonly || $disabled)
{
	$attribs[] = 'style="pointer-events: none"';
}

?>
<fieldset id="<?php echo $id; ?>" >
	<div <?php echo implode(' ', $attribs); ?>>
		<?php foreach ($options as $i => $option) : ?>
			<?php
			$disabled    = !empty($option->disable) ? 'disabled' : '';
			$style       = $disabled ? 'style="pointer-events: none"' : '';

			// Initialize some option attributes.
			if ($isBtnYesNo)
			{
				// Set the button classes for the yes/no group
				if ($option->value === "0")
				{
					$optionClass = 'btn btn-outline-danger';
				}
				else
				{
					$optionClass = 'btn btn-outline-success';
				}
			}
			else
			{
				$optionClass = !empty($option->class) ? $option->class : $btnClass;
				$optionClass = trim($optionClass . ' ' . $disabled);
			}
			$checked     = ((string) $option->value === $value) ? 'checked="checked"' : '';
			$optionClass .= $checked ? ' active' : '';

			// Initialize some JavaScript option attributes.
			$onclick    = !empty($option->onclick) ? 'onclick="' . $option->onclick . '"' : '';
			$onchange   = !empty($option->onchange) ? 'onchange="' . $option->onchange . '"' : '';
			$oid        = $id . $i;
			$ovalue     = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
			$attributes = array_filter(array($checked, $optionClass, $disabled, $style, $onchange, $onclick));
			?>
			<?php if ($required) : ?>
				<?php $attributes[] = 'required'; ?>
			<?php endif; ?>
			<label for="<?php echo $oid; ?>" class="<?php echo trim($optionClass . ' ' . $style);; ?>">
				<input type="radio" id="<?php echo $oid; ?>" name="<?php echo $name; ?>" value="<?php echo $ovalue; ?>" <?php echo implode(' ', $attributes); ?>>
				<?php echo $option->text; ?>
			</label>
		<?php endforeach; ?>
	</div>
</fieldset>
