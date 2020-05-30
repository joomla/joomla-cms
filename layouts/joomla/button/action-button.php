<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.popover');

/**
 * @var $icon    string
 * @var $title   string
 * @var $value   string
 * @var $task    string
 * @var $options array
 */
extract($displayData, EXTR_OVERWRITE);

$disabled = !empty($options['disabled']);
$taskPrefix = $options['task_prefix'];
$checkboxName = $options['checkbox_name'];
$tip = !empty($options['tip']);
$tipTitle = $options['tip_title'];
?>
<button type="submit" class="tbody-icon data-state-<?php echo $this->escape($value ?? ''); ?><?php echo $tip ? ' hasPopover' : ''; ?>"
		title="<?php echo HTMLHelper::_('tooltipText', $tipTitle ?: $title, '', 0); ?>"
		data-content="<?php echo HTMLHelper::_('tooltipText', $title, '', 0); ?>"
		data-placement="top"
	<?php echo !empty($disabled) ? 'disabled' : ''; ?>
	<?php if (!empty($task) && empty($disabled)) : ?>
		onclick="return Joomla.listItemTask('<?php echo $checkboxName . $this->escape($row ?? ''); ?>', '<?php echo $this->escape(isset($task) ? $taskPrefix . $task : ''); ?>')"
	<?php endif; ?>
>
	<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
	<span class="sr-only"><?php echo $title; ?></span>
</button>
