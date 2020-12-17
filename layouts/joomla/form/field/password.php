<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
 * @var   array    $inputType       Options available for this field.
 * @var   string   $accept          File types that are accepted.
 * @var   boolean  $lock            Is this field locked.
 */

if ($meter)
{
	JHtml::_('script', 'system/passwordstrength.js', array('version' => 'auto', 'relative' => true, 'framework' => true));

	// Load script on document load.
	JFactory::getDocument()->addScriptDeclaration(
		"
		jQuery(document).ready(function() {
			new Form.PasswordStrength('" . $id . "',
				{
					threshold: " . $threshold . ",
					onUpdate: function(element, strength, threshold) {
						element.set('data-passwordstrength', strength);
					}
				});
		});"
	);
}

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));

if ($lock)
{
	// Load script on document load.
	JFactory::getDocument()->addScriptDeclaration(
			"
		jQuery(document).ready(function() {
			jQuery('#" . $id ."_lock').on('click', function() {
				var lockButton = jQuery(this);
				var passwordInput = jQuery('#" . $id . "');
				var lock = lockButton.hasClass('active');

				if (lock === true) {
					lockButton.html('" . JText::_('JMODIFY', true) . "');
					passwordInput.attr('disabled', true);
					passwordInput.val('');
				}
				else
				{
					lockButton.html('" . JText::_('JCANCEL', true) . "');
					passwordInput.attr('disabled', false);
				}
			});
		});"
	);

	$disabled = true;
	$hint = str_repeat('*', strlen($value));
	$value = '';
}

$attributes = array(
	strlen($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : '',
	!$autocomplete ? 'autocomplete="off"' : '',
	!empty($class) ? 'class="' . $class . '"' : '',
	$readonly ? 'readonly' : '',
	$disabled ? 'disabled' : '',
	!empty($size) ? 'size="' . $size . '"' : '',
	!empty($maxLength) ? 'maxlength="' . $maxLength . '"' : '',
	$required ? 'required aria-required="true"' : '',
	$autofocus ? 'autofocus' : '',
);

?>
<?php if ($lock): ?>
    <span class="input-append">
<?php endif; ?>
<input
    type="password"
    name="<?php echo $name; ?>"
    id="<?php echo $id; ?>"
    value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
    <?php echo implode(' ', $attributes); ?>
/>
<?php if ($lock): ?>
    <button type="button" id="<?php echo $id; ?>_lock" class="btn btn-info" data-toggle="button"><?php echo JText::_('JMODIFY'); ?></button>
    </span>
<?php endif; ?>
