<?php
/**
 * Part of 40dev project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var $icon    string
 * @var $title   string
 * @var $value   string
 * @var $task    string
 * @var $options array
 */
extract($displayData, EXTR_OVERWRITE);

$only_icon = !empty($options['only_icon']);
$disabled = !empty($options['disabled']);
$tip = !empty($options['tip']);
$tipTitle = $options['tip_title'];
$taskPrefix = $options['task_prefix'];
$checkboxName = $options['checkbox_name'];
?>
<?php if($only_icon): ?>
	<span class="<?php echo $this->escape(isset($icon) ? $icon : ''); ?> <?php echo $tip ? 'hasPopover' : '' ?>"
		title="<?php echo HTMLHelper::_('tooltipText', JText::_($tipTitle ? : $title), '', 0) ?>"
		data-content="<?php echo HTMLHelper::_('tooltipText', JText::_($title), '', 0) ?>"
		data-placement="top"
	></span>
<?php else: ?>
	<a class="tbody-icon data-state-<?php echo $this->escape(isset($value) ? $value : ''); ?> <?php echo $this->escape(!empty($disabled) ? 'disabled' : null); ?> <?php echo $tip ? 'hasPopover' : '' ?>"
		<?php if (empty($disabled)): ?>
			href="javascript://"
		<?php endif; ?>

		title="<?php echo HTMLHelper::_('tooltipText', JText::_($tipTitle ? : $title), '', 0) ?>"
		data-content="<?php echo HTMLHelper::_('tooltipText', JText::_($title), '', 0) ?>"
		data-placement="top"

		<?php if(!empty($task) && empty($disabled)): ?>
			onclick="return listItemTask('<?php echo $checkboxName . $this->escape(isset($row) ? $row : ''); ?>', '<?php echo $this->escape(isset($task) ? $taskPrefix . $task : ''); ?>')"
		<?php endif; ?>
	>
		<span class="<?php echo $this->escape(isset($icon) ? $icon : null); ?>" aria-hidden="true"></span>
	</a>
<?php endif; ?>
