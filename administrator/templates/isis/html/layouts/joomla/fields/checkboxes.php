<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', false, true);

// Initialize some field attributes.
$autofocus        = $displayData['autofocus'];
$fieldclass       = $displayData['class'].' checkboxes';
$field            = $displayData['field'];
$options          = $displayData['options'];
$required         = $displayData['required'];

// init checked options (note : this was before in the foreach loop for nothing)
$isEmpty         = !isset($displayData['value']) || empty($displayData['value']);
if ($isEmpty) {
	// nothing is set? use default checkedOptions from xml
	$checkedOptions  = $displayData['checkedOptions'] ;
}
else
{
	$checkedOptions  = !is_array($displayData['value']) ? explode(',', $displayData['value']) : $displayData['value'];
}

?>
<fieldset id="<?php echo $field->id; ?>" class="<?php echo $fieldclass; ?>"
	<?php echo $required ? 'required aria-required="true"' : '';?>
	<?php echo $autofocus ? 'autofocus' : ''; ?>>

	<?php if (!empty($options)) : ?>
	<ul>
		<?php 
		foreach ($options as $i => $option) {
			// Initialize some option attributes.
			$checked = in_array((string) $option->value, $checkedOptions) ? 'checked' : '';
			$class = !empty($option->class) ? 'class="' . $option->class . '"' : '';
			$disabled = !empty($option->disable) || $field->disabled ? 'disabled' : '';

			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? 'onclick="' . $option->onclick . '"' : '';
			$onchange = !empty($option->onchange) ? 'onchange="' . $option->onchange . '"' : '';
			?>
			<li>
				<input type="checkbox" id="<?php echo $displayData['field']->id . $i; ?>" name="<?php echo $displayData['field']->name; ?>" value="<?php echo
					. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8'); ?>"<?php echo $checked . $class . $onclick . $onchange . $disabled; ?>/>
				<label for="<?php echo $id; ?>" <?php echo $class; ?>><?php echo JText::_($option->text); ?></label>
			</li>
		<?php } ?>
	</ul>
	<?php endif; ?>
</fieldset>
