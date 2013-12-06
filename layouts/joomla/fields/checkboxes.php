<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$autofocus        = $displayData['autofocus'];
$checkedOptions   = $displayData['checkedOptions'];
$classes          = $displayData['classes'];
$field            = $displayData['field'];
$hasValue         = $displayData['hasValue'];
$options          = $displayData['options'];
$required         = $displayData['required'];

$classes[] = 'checkboxes';

$format = '<input type="checkbox" id="%1$s" name="%2$s" value="%3$s" %4$s />';
?>

<fieldset id="<?php echo $field->id; ?>" class="<?php echo implode(' ', $classes); ?>"
	<?php echo $required ? 'required aria-required="true"' : '';?>
	<?php echo $autofocus ? 'autofocus' : ''; ?>>
<ul>
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

		<li>
			<?php echo sprintf($format, $id, $field->name, $value, implode(' ', $attributes)); ?>
			<label for="<?php echo $id; ?>" <?php echo $class; ?>><?php echo JText::_($option->text); ?></label>
		</li>

	<?php endforeach; ?>
</ul>
</fieldset>
