<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

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

$alt        = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
$dataToggle = (strpos(trim($class), 'btn-group') !== false) ? ' data-toggle="buttons"' : '';

// Add the attributes of the fieldset in an array
$attribs = [
	'id="' . $id . '"',
	'class="' . trim($class . ' radio') . '"',
];

if (!empty($disabled))
{
	$attribs[] = 'disabled';
}

if (!empty($required))
{
	$attribs[] = 'required aria-required="true"';
}

if (!empty($autofocus))
{
	$attribs[] = 'autofocus';
}

if (!empty($dataToggle))
{
	$attribs[] = $dataToggle;
}

?>
<fieldset <?php echo implode(' ', $attribs); ?>>
	<?php foreach ($options as $i => $option) : ?>
		<?php
		// Initialize some option attributes.
		$checked     = ((string) $option->value === $value) ? 'checked="checked"' : '';
		$optionClass = !empty($option->class) ? 'class="' . $option->class . '"' : '';
		$disabled    = !empty($option->disable) || ($disabled && !$checked) ? 'disabled' : '';

		// Initialize some JavaScript option attributes.
		$onclick    = !empty($option->onclick) ? 'onclick="' . $option->onclick . '"' : '';
		$onchange   = !empty($option->onchange) ? 'onchange="' . $option->onchange . '"' : '';
		$oid        = $id . $i;
		$ovalue     = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
		$attributes = array_filter(array($checked, $optionClass, $disabled, $onchange, $onclick));
		?>
		<?php if ($required) : ?>
			<?php $attributes[] = 'required aria-required="true"'; ?>
		<?php endif; ?>
		<label for="<?php echo $oid; ?>" <?php echo $optionClass; ?>>
			<input type="radio" id="<?php echo $oid; ?>" name="<?php echo $name; ?>" value="<?php echo $ovalue; ?>" <?php echo implode(' ', $attributes); ?>>
			<?php echo $option->text; ?>
		</label>
	<?php endforeach; ?>
</fieldset>
