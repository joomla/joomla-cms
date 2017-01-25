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
$class = $field->params->get('render_class');
$hidelabel = $field->params->get('hidelabel');

if ($value == '')
{
	return;
}

?>

<dd class="field-entry <?php echo $class; ?>" id="field-entry-<?php echo $field->id; ?>">
	<?php if ($hidelabel == 0) : ?>
	<span class="field-label"><?php echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>: </span>
	<?php endif; ?>
	<span class="field-value"><?php echo $value; ?></span>
</dd>
