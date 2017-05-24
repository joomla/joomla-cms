<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if (!key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$label = $field->label;
$value = $field->value;
$class = $field->params->get('render_class');

if (!$value)
{
	return;
}

if ($field->context == 'com_contact.mail')
{
	// Prepare the value for the contact form mail
	echo $label . ': ' . $value . "\r\n";
	return;
}

?>

<dt class="contact-field-entry <?php echo $class; ?>" id="contact-field-entry-label-<?php echo $field->id; ?>">
	<span class="field-label"><?php echo htmlentities($label); ?>: </span>
</dt>
<dd class="contact-field-entry <?php echo $class; ?>" id="contact-field-entry-value-<?php echo $field->id; ?>">
	<span class="field-value"><?php echo $value; ?></span>
</dd>
