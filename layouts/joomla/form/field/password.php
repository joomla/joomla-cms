<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
 * @var   boolean  $spellchec       Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $spellcheck      Enable spell check for this field.
 * @var   array    $checked         Is this field checked?
 *
 * @var   boolean  $meter           Is the password strength indicator enabled?
 * @var   string   $username        The field that contains the username
 * @var   integer  $minLength       The minimum characters allowed
 */

// Initialize some field attributes.
$size            = !empty($size) ? 'size="' . $size . '"' : '';
$class           = !empty($class) ? 'class="' . $class . '"' : '';
$readonly        = $readonly ? 'readonly' : '';
$disabled        = $disabled ? 'disabled' : '';
$required        = $required ? 'required aria-required="true"' : '';
$hint            = $hint ? ' placeholder="' . $hint . '"' : '';
$autocomplete    = !$autocomplete ? 'autocomplete="off"' : '';
$autofocus       = $autofocus ? 'autofocus' : '';
$spellcheck      = $spellcheck ? '' : 'spellcheck="false"';
$maxLength       = !empty($maxLength) ? 'maxlength="' . $maxLength . '"' : '';
$multiple        = !empty($multiple) ? 'multiple' : '';

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', false, true);

if ($meter)
{
	JHtml::_('bootstrap.framework');
	JHtml::_('script', 'jui/pwstrength-bootstrap.min.js', false, true, false, false, true);

	// Load script on document load.
	JFactory::getDocument()->addScriptDeclaration(
		"
		jQuery(document).ready(function($){
			'use strict';
			var options = {};
			options.common = {};
			" . $username . "
			options.common.minChar = " . $minLength . ";
			options.ui = {
				bootstrap2: true,
				showErrors: true,
			};
			options.ui.verdicts = [
			'" . JText::_('JFIELD_PASSWORD_INDICATE_VERYWEAK') . "',
			'" . JText::_('JFIELD_PASSWORD_INDICATE_WEAK') . "',
			'" . JText::_('JFIELD_PASSWORD_INDICATE_NORMAL') . "',
			'" . JText::_('JFIELD_PASSWORD_INDICATE_MEDIUM') . "',
			'" . JText::_('JFIELD_PASSWORD_INDICATE_STRONG') . "',
			'" . JText::_('JFIELD_PASSWORD_INDICATE_VERYSTRONG') . "'];
			options.ui.errorMessages = {
				wordLength: '" . JText::_('JFIELD_PASSWORD_INDICATE_LENGTH') . "',
				wordNotEmail: '" . JText::_('JFIELD_PASSWORD_INDICATE_NOEMAIL') . "',
				wordSequences: '" . JText::_('JFIELD_PASSWORD_INDICATE_WORDSEQ') . "',
				wordRepetitions: '" . JText::_('JFIELD_PASSWORD_INDICATE_WORDREP') . "',
				wordSimilarToUsername: '" . JText::_('JFIELD_PASSWORD_INDICATE_USERNAME') . "',
				wordTwoCharacterClasses: '" . JText::_('JFIELD_PASSWORD_INDICATE_CHARCLASS') . "',
			};
			jQuery('#" . $id . "').pwstrength(options);
		});
		"
	);
}
?>
<input type="password" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php
echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" <?php
echo $hint, $autocomplete, $class, $readonly, $disabled, $size, $maxLength, $required, $autofocus; ?> />