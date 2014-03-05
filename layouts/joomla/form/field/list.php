<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * ------------------
 * 	- id        : (string) DOM id of the element
 * 	- class     : (string) CSS class to apply
 * 	- size      : (integer) Size for the input element
 * 	- element   : (SimpleXMLElement) The object of the <field /> XML element that describes the form field.
 * 	- field     : (JFormField) Object to access to the field properties
 * 	- multiple  : (boolean) Allow to enter multiple values?
 * 	- name      : (string) Name of the field to display
 * 	- required  : (boolean) Is this field required?
 * 	- value     : (mixed) Value of the field
 * 	- autofocus : Autofocus on this field
 *
 */

$attributes = array();

$attributes['id']            = $id;
$attributes['class']         = !empty($class) ? (string) $class : null;
$attributes['size']          = !empty($size) ? (int) $size : null;
$attributes['multiple']      = $multiple ? 'multiple' : null;
$attributes['required']      = $required ? 'required' : null;
$attributes['aria-required'] = $required ? 'true' : null;
$attributes['onchange']      = $element['onchange'] ? (string) $element['onchange'] : null;
$attributes['autofocus']     = $autofocus ? ' autofocus' : null;

if ((string) $field->readonly == '1' || (string) $field->readonly == 'true' || (string) $field->disabled == '1'|| (string) $field->disabled == 'true')
{
	$attributes['disabled'] = 'disabled';
}

$renderedAttributes = null;

if ($attributes)
{
	foreach ($attributes as $attribName => $attribValue)
	{
		if (null !== $attribValue)
		{
			$renderedAttributes .= ' ' . $attribName . '="' . (string) $attribValue . '"';
		}
	}
}

$readOnly = ((string) $element['readonly'] == 'true');

// If it's readonly the select will have no name
$selectName = $readOnly ? '' : $name;
?>
<select name="<?php echo $selectName; ?>" <?php echo $renderedAttributes; ?>>
	<?php if ($options) : ?>
		<?php foreach ($options as $optionKey => $optionValue) :?>
				<?php
					// Force object to array conversion
					$optionValue = (array) $optionValue;

					// BC: Some special cases come in the format [value] => text
					$option = array(
						'text' => isset($optionValue['text']) ? $optionValue['text'] : $optionValue[0],
						'value' => isset($optionValue['value']) ? $optionValue['value'] : $optionKey
					);

					// Value can be an array or a string
					$selected = is_array($value) ? in_array($option['value'], $value) : ($option['value'] == $value);
				?>
				<option value="<?php echo $option['value']; ?>" <?php if ($selected) : ?>selected="selected"<?php endif; ?>>
					<?php echo $option['text']; ?>
				</option>
		<?php endforeach; ?>
	<?php endif; ?>

</select>
<?php if ($readOnly) : ?>
	<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
<?php endif;