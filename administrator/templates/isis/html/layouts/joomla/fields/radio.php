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

$data = new JRegistry($displayData);

// Always use the 'radio' class.
$classes = $data->get('classes', array());
$classes[] = 'radio';

/**
 * The format of the input tag to be filled in using sprintf.
 *     %1 - id
 *     %2 - name
 *     %3 - value
 *     %4 = any other attributes
 */
$format = '<input type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s />';

$id = $data->get('id', '');
$name = $data->get('name', '');
$value = $data->get('value', '');

// The alt option for JText::alt
$alt = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
?>

<fieldset id="<?php echo $id; ?>" class="<?php echo implode(' ', $classes); ?>"
	<?php echo $data->get('disabled') ? 'disabled' : '';?>
	<?php echo $data->get('required') ? 'required aria-required="true"' : '';?>
	<?php echo $data->get('autofocus') ? 'autofocus' : ''; ?>>

	<?php foreach ($data->get('options') as $i => $option) : ?>
		<?php
		// Initialize some option attributes.
		$checked = ((string) $option->value == $value) ? 'checked' : '';

		$class = !empty($option->class) ? 'class="' . $option->class . '"' : '';
		$disabled = !empty($option->disable) || ($data->get('disabled') && !$checked) ? 'disabled' : '';

		// Initialize some JavaScript option attributes.
		$onclick = !empty($option->onclick) ? 'onclick="' . $option->onclick . '"' : '';
		$onchange = !empty($option->onchange) ? 'onchange="' . $option->onchange . '"' : '';

		$oid = $id . $i;
		$ovalue = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
		$attributes = array_filter(array($checked, $class, $disabled, $onchange, $onclick));

		if ($data->get('required'))
		{
			$attributes[] = 'required aria-required="true"';
		}
		?>

		<label for="<?php echo $id; ?>" class="radio">
			<?php echo sprintf($format, $oid, $name, $ovalue, implode(' ', $attributes)); ?>
		<?php echo JText::alt($option->text, $alt); ?></label>

	<?php endforeach; ?>
</fieldset>
