<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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

// If there are no options don't render anything
if (empty($options))
{
	return '';
}

/**
 * The format of the input tag to be filled in using sprintf.
 *     %1 - id
 *     %2 - name
 *     %3 - value
 *     %4 = any other attributes
 */
$format     = '<input type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s>';
$alt        = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);

HTMLHelper::_('webcomponent',
	'system/webcomponents/joomla-field-switcher.min.js',
	['relative' => true, 'version' => 'auto']
);

// Set the type of switcher
$type = '';

if ($pos = strpos($class, 'switcher-'))
{
	$type = 'type="' . strtok(substr($class, $pos + 9), ' ') . '"';
}

// Add the attributes of the fieldset in an array
$attribs = [
	'id="' . $id . '"',
	$type,
	'off-text="' . $options[0]->text . '"',
	'on-text="' . $options[1]->text . '"',
];

if (!empty($disabled))
{
	$attribs[] = 'disabled';
}

if (!empty($onclick))
{
	$attribs[] = 'onclick="' . $onclick . '()"';
}

if (!empty($onchange))
{
	$attribs[] = 'onchange="' . $onchange . '()"';
}

?>
<joomla-field-switcher <?php echo implode(' ', $attribs); ?>>
	<?php foreach ($options as $i => $option) : ?>
		<?php
		// Initialize some option attributes.
		$checked = ((string) $option->value == $value) ? 'checked="checked"' : '';
		$active  = ((string) $option->value == $value) ? 'class="active"' : '';

		// Initialize some JavaScript option attributes.
		$oid        = $id . $i;
		$ovalue     = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
		$attributes = array_filter(array($checked, $active, null));
		?>
		<?php echo sprintf($format, $oid, $name, $ovalue, implode(' ', $attributes)); ?>
	<?php endforeach; ?>
</joomla-field-switcher>
