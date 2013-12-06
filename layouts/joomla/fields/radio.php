<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$autofocus = $displayData['autofocus'];
$classes   = $displayData['classes'];
$disabled  = $displayData['disabled'];
$field     = $displayData['field'];
$options   = $displayData['options'];
$readonly  = $displayData['readonly'];
$required  = $displayData['required'];
$value     = $displayData['value'];

$classes[] = 'radio';

$format = '<input type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s />';

$alt = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $field->fieldname);
?>

<fieldset id="<?php echo $field->id; ?>" class="<?php echo implode(' ', $classes); ?>"
	<?php echo $disabled ? 'disabled' : '';?>
	<?php echo $required ? 'required aria-required="true"' : '';?>
	<?php echo $autofocus ? 'autofocus' : ''; ?>>
<ul>
	<?php foreach ($options as $i => $option) : ?>
		<?php
		// Initialize some option attributes.
		$checked = ((string) $option->value == $value) ? 'checked' : '';

		$class = !empty($option->class) ? 'class="' . $option->class . '"' : '';
		$disabled = !empty($option->disable) || ($readonly && !$checked) ? 'disabled' : '';

		// Initialize some JavaScript option attributes.
		$onclick = !empty($option->onclick) ? 'onclick="' . $option->onclick . '"' : '';
		$onchange = !empty($option->onchange) ? 'onchange="' . $option->onchange . '"' : '';

		$id = $field->id . $i;
		$value = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
		$attributes = array_filter(array($checked, $class, $disabled, $onchange, $onclick));

		if ($required)
		{
			$attributes[] = 'required aria-required="true"';
		}
		?>

		<li>
			<?php echo sprintf($format, $id, $field->name, $value, implode(' ', $attributes)); ?>
			<label for="<?php echo $id; ?>" <?php echo $class; ?>><?php echo JText::alt($option->text, $alt); ?></label>
		</li>

	<?php endforeach; ?>
</ul>
</fieldset>
