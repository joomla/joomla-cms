<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * If true, the field should get an 'autofocus' attribute.
 *
 * @var boolean
 */
$autofocus = $displayData['autofocus'];

/**
 * A list of options that should be checked. These may either be stored or default values (from the field element).
 *
 * @var array
 */
$checkedOptions = $displayData['checkedOptions'];

/**
 * A list of classes for this field.
 *
 * @var array
 */
$classes = $displayData['classes'];

/**
 * The field object.
 *
 * @var JFormField
 */
$field = $displayData['field'];

/**
 * If true, the field has a stored value so that default values should not be used.
 *
 * @var boolean
 */
$hasValue = $displayData['hasValue'];

/**
 * A list of options for the field.
 *
 * @var array
 */
$options = $displayData['options'];

/**
 * If true, this is a required field.
 *
 * @var boolean
 */
$required = $displayData['required'];

// Always use the 'checkboxes' class.
$classes[] = 'checkboxes';

// The format of the input tag to be filled in using sprintf.
//     %1 - id
//     %2 - name
//     %3 - value
//     %4 = any other attributes
$format = '<input type="checkbox" id="%1$s" name="%2$s" value="%3$s" %4$s />';

// The alt option for JText::alt
$alt = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $field->fieldname);
?>

<fieldset id="<?php echo $field->id; ?>" class="<?php echo implode(' ', $classes); ?>"
	<?php echo $required ? 'required aria-required="true"' : '';?>
	<?php echo $autofocus ? 'autofocus' : ''; ?>>

	<?php foreach ($options as $i => $option) : ?>
		<?php
		// Initialize some option attributes.
		$checked = in_array((string) $option->value, $checkedOptions) ? 'checked' : '';

		// In case there is no stored value, use the option's default state.
		$checked = (!$hasValue && $option->checked) ? 'checked' : $checked;

		$class = !empty($option->class) ? 'class="' . $option->class . '"' : '';
		$disabled = !empty($option->disable) || $field->disabled ? 'disabled' : '';

		// Initialize some JavaScript option attributes.
		$onclick = !empty($option->onclick) ? 'onclick="' . $option->onclick . '"' : '';
		$onchange = !empty($option->onchange) ? 'onchange="' . $option->onchange . '"' : '';

		$id = $field->id . $i;
		$value = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
		$attributes = array_filter(array($checked, $class, $disabled, $onchange, $onclick));
		?>

		<label for="<?php echo $id; ?>" class="checkbox">
			<?php echo sprintf($format, $id, $field->name, $value, implode(' ', $attributes)); ?>
		<?php echo JText::alt($option->text, $alt); ?></label>

	<?php endforeach; ?>
</fieldset>
