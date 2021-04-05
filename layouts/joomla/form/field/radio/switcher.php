<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

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
 * @var   array    $options         Options available for this field.
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attributes for eg, data-*.
 */

// If there are no options don't render anything
if (empty($options))
{
	return '';
}

// Load the css files
Factory::getApplication()->getDocument()->getWebAssetManager()->useStyle('switcher');

/**
 * The format of the input tag to be filled in using sprintf.
 *     %1 - id
 *     %2 - name
 *     %3 - value
 *     %4 = any other attributes
 */
$input = '<input type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s>';

$attr = 'id="' . $id . '"';
$attr .= $onchange ? ' onchange="' . $onchange . '"' : '';
$attr .= $dataAttribute;

?>
<fieldset <?php echo $attr; ?>>
	<legend class="visually-hidden">
		<?php echo $label; ?>
	</legend>
	<div class="switcher<?php echo ($readonly || $disabled ? ' disabled' : ''); ?>">
	<?php foreach ($options as $i => $option) : ?>
		<?php
		// False value casting as string returns an empty string so assign it 0
		if (empty($value) && $option->value == '0')
		{
			$value = '0';
		}

		// Initialize some option attributes.
		$optionValue = (string) $option->value;
		$optionId    = $id . $i;
		$attributes  = $optionValue == $value ? 'checked class="active"' : '';
		$attributes  .= $optionValue != $value && $readonly || $disabled ? ' disabled' : '';
		?>
		<?php echo sprintf($input, $optionId, $name, $this->escape($optionValue), $attributes); ?>
		<?php echo '<label for="' . $optionId . '">' . $option->text . '</label>'; ?>
	<?php endforeach; ?>
	<span class="toggle-outside"><span class="toggle-inside"></span></span>
	</div>
</fieldset>
