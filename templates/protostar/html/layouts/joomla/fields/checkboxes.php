<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = new JRegistry($displayData);
$data = new JRegistry($displayData);

$data = new JRegistry($displayData);

// Always use the 'checkboxes' class.
$classes = $data->get('classes', array());
$classes[] = 'checkboxes';

/**
 * The format of the input tag to be filled in using sprintf.
 *     %1 - id
 *     %2 - name
 *     %3 - value
 *     %4 = any other attributes
 */
$format = '<input type="checkbox" id="%1$s" name="%2$s" value="%3$s" %4$s />';

$id = $data->get('id', '');
$name = $data->get('name', '');

// The alt option for JText::alt
$alt = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);

?>

<fieldset id="<?php echo $id; ?>" class="<?php echo implode(' ', $classes); ?>"
	<?php echo $data->get('required') ? 'required aria-required="true"' : '';?>
	<?php echo $data->get('autofocus') ? 'autofocus' : ''; ?>>

	<?php foreach ($data->get('options', array()) as $i => $option) : ?>
	<?php
	// Initialize some option attributes.
	$checked = in_array((string) $option->value, $data->get('checkedOptions', array())) ? 'checked' : '';

	// In case there is no stored value, use the option's default state.
	$checked = (!$data->get('hasValue') && $option->checked) ? 'checked' : $checked;

	$class = !empty($option->class) ? 'class="' . $option->class . '"' : '';
	$disabled = !empty($option->disable) || $data->get('disabled') ? 'disabled' : '';

	// Initialize some JavaScript option attributes.
	$onclick = !empty($option->onclick) ? 'onclick="' . $option->onclick . '"' : '';
	$onchange = !empty($option->onchange) ? 'onchange="' . $option->onchange . '"' : '';

	$oid = $id . $i;
	$value = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
	$attributes = array_filter(array($checked, $class, $disabled, $onchange, $onclick));
	?>

	<label for="<?php echo $oid; ?>" class="checkbox">
		<?php echo sprintf($format, $oid, $name, $value, implode(' ', $attributes)); ?>
	<?php echo JText::alt($option->text, $alt); ?></label>

	<?php endforeach; ?>
</fieldset>
