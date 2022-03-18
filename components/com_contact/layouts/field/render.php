<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if (!key_exists('field', $displayData))
{
	return;
}

$field     = $displayData['field'];
$label     = JText::_($field->label);
$value     = $field->value;
$class     = $field->params->get('render_class');
$showLabel = $field->params->get('showlabel');
$labelClass = $field->params->get('label_render_class');

if ($field->context == 'com_contact.mail')
{
	// Prepare the value for the contact form mail
	$value = html_entity_decode($value);

	echo ($showLabel ? $label . ': ' : '') . $value . "\r\n";
	return;
}

if (!strlen($value))
{
	return;
}

?>
<dt class="contact-field-entry <?php echo $class; ?>">
	<?php if ($showLabel == 1) : ?>
		<span class="field-label <?php echo $labelClass; ?>"><?php echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>: </span>
	<?php endif; ?>
</dt>
<dd class="contact-field-entry <?php echo $class; ?>">
	<span class="field-value"><?php echo $value; ?></span>
</dd>
