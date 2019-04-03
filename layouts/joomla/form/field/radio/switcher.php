<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
$input    = '<input type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s>';
$alt      = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);

// HTMLHelper::_('stylesheet', 'system/fields/joomla-field-switcher.css', ['version' => 'auto', 'relative' => true]);



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
<style>

.switcher {
  position: relative;
  width: 18rem;
  height: 3rem;
}
.switcher input {
  position: absolute;
  top: 0;
  z-index: 2;
  opacity: 0;
  cursor: pointer;
  height: 3rem;
  width: 6rem;
  margin: 0;
}
.switcher input:checked {
  z-index: 1;
}
.switcher input:checked + label {
  opacity: 1;
}
.switcher input:not(:checked) + label {
  opacity: 0;
}
.switcher label {
  line-height: 3rem;
  display: inline-block;
  width: 6rem;
  height: 100%;
  margin-left: 6.5em;
  text-align: left;
  position: absolute;
  transition: opacity 0.25s ease;
}
.switcher .toggle-outside {
  height: 100%;
  padding: 0.25rem;
  overflow: hidden;
  transition: 0.25s ease all;
  background: green;
  position: absolute;
  width: 6rem;
  box-sizing: border-box;

}
.switcher .toggle-inside {
  height: 2.5rem;
  width: 2.5rem;  
  background: white;
  position: absolute;
  transition: 0.25s ease all;
}
.switcher input:checked ~ .toggle-outside .toggle-inside {
  left: 0.25rem;
}
.switcher input ~ input:checked ~ .toggle-outside .toggle-inside {
  left: 3.25rem;
}
.switcher__legend {
 margin-bottom: 1rem;
 font-size: 1rem;
 font-weight: 400;
}

</style>
<fieldset>
  <legend class="switcher__legend">
    <?php echo htmlspecialchars($label, ENT_COMPAT, 'UTF-8'); ?>
  </legend>
  <div class="switcher" role="switch">
  <?php foreach ($options as $i => $option) : ?>
    <?php
    // Initialize some option attributes.
    $checked = ((string) $option->value == $value) ? 'checked="checked"' : '';
    $active  = ((string) $option->value == $value) ? 'class="active"' : '';

    // Initialize some option attributes.
    $oid        = $id . $i;
    $ovalue     = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
    $attributes = array_filter(array($checked, $active, null));
    $text       = $options[$i]->text;
    ?>
    <?php echo sprintf($input, $oid, $name, $ovalue, implode(' ', $attributes)); ?>
    <?php echo '<label for="' . $oid . '">' . $text . '</label>'; ?>
	<?php endforeach; ?>
  <span class="toggle-outside"><span class="toggle-inside"></span></span>
  </div>
</fieldset>
