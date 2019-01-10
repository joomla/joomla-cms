<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if (!key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$label = JText::_($field->label);
$value = $field->value;
$showLabel = $field->params->get('showlabel');
$labelClass = $field->params->get('label_render_class');
$showSuffix = $field->params->get('showsuffix');
$suffix = $field->params->get('suffix');


if ($value == '')
{
	return;
}

?>
<?php if ($showLabel == 1) : ?>
	<span class="field-label <?php echo $labelClass; ?>"><?php echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>: </span>
<?php endif; ?>
<span class="field-value"><?php echo $value; ?></span>
<?php if ($showSuffix == 1) : ?>
	<span class="field-suffix"><?php echo htmlentities($suffix, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?></span>
<?php endif; ?>
