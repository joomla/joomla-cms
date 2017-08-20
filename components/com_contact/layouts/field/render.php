<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (empty($field = $displayData['field']) || !($field instanceof stdClass))
{
	return;
}

if ($field->context == 'com_contact.mail')
{
	echo ($showLabel ? $field->label . ': ' : '') . html_entity_decode($field->value) . "\r\n";

	return;
}

if (empty($field->value))
{
	return;
}

$showLabel = (bool) $field->params->get('showlabel', true);

$renderClass = 'contact-field-entry';

if (($renderClassSuffix = $field->params->get('render_class', '')))
{
	$renderClass .= ' ' . $renderClassSuffix;
}

?>
<dt class="<?php echo $this->escape($renderClass); ?>">
<?php if ($showLabel) : ?>
	<span class="field-label"><?php echo $this->escape($field->label); ?>:</span>
<?php endif; ?>
</dt>
<dd class="<?php echo $this->escape($renderClass); ?>">
	<span class="field-value"><?php echo $this->escape($field->value); ?></span>
</dd>
