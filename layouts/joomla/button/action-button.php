<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.popover');

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
	<span class="<?php echo $this->escape($icon ?? ''); ?> <?php echo $tip ? 'hasPopover' : '' ?>"
		title="<?php echo HTMLHelper::_('tooltipText', Text::_($tipTitle ? : $title), '', 0) ?>"
		data-content="<?php echo HTMLHelper::_('tooltipText', Text::_($title), '', 0) ?>"
		data-placement="top"
	></span>
<?php else: ?>
	<a class="tbody-icon data-state-<?php echo $this->escape($value ?? ''); ?> <?php echo $this->escape(!empty($disabled) ? 'disabled' : null); ?> <?php echo $tip ? 'hasPopover' : '' ?>"
		<?php if (empty($disabled)): ?>
			href="javascript://"
		<?php endif; ?>

		title="<?php echo HTMLHelper::_('tooltipText', Text::_($tipTitle ? : $title), '', 0) ?>"
		data-content="<?php echo HTMLHelper::_('tooltipText', Text::_($title), '', 0) ?>"
		data-placement="top"

		<?php if(!empty($task) && empty($disabled)): ?>
			onclick="return listItemTask('<?php echo $checkboxName . $this->escape($row ?? ''); ?>', '<?php echo $this->escape(isset($task) ? $taskPrefix . $task : ''); ?>')"
		<?php endif; ?>
	>
		<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
	</a>
<?php endif; ?>
