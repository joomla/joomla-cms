<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
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
$label = JText::_($field->label);
$value = $field->value;
$labelClass = $field->params->get('label_render_class');
$showLabel = $field->params->get('showlabel');
if ($showLabel == 0)
{
$labelClass .= ' hidden';
}


if ($value == '')
{
	return;
}

?>
<span class="field-label <?php echo $labelClass; ?>"><?php echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>: </span>
<span class="field-value"><?php echo $value; ?></span>
