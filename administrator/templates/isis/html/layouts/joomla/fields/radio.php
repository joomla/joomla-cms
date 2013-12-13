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
$class = $displayData['class'] ? ' class="radio ' .  $displayData['class'] . '"' : ' class="radio"';
$required  = $displayData['required'] ? ' required aria-required="true"' : '';
$autofocus = $displayData['autofocus'] ? ' autofocus' : '';
$disabled  = $displayData['disabled'] ? ' disabled' : '';
$readonly  = $displayData['readonly'];
?>
<fieldset id="<?php echo $displayData['field']->id ?>"<?php echo $class . $required . $autofocus . $disabled; ?>>
	<?php
	foreach ($displayData['options'] as $i => $option)
	{
		// Initialize some option attributes.
		$checked = (string) $option->value == $displayData['value'] ? ' checked="checked"' : '';
		$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';

		$disabled = !empty($option->disable) || ($readonly && !$checked);
		$disabled = $disabled ? ' disabled' : '';

		// Initialize some JavaScript option attributes.
		$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
		?>
		<label for="<?php echo $displayData['field']->id . $i; ?>"<?php echo $class; ?>><?php echo
		 JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $displayData['field']->fieldname)); ?>
			<input type="radio" id="<?php echo $displayData['field']->id . $i; ?>" name="<?php echo $displayData['field']->name; ?>" value="<?php echo
			 htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8'); ?>"<?php echo $checked . $class . $onclick . $disabled; ?>/>
		</label>
	<?php } ?>
</fieldset>