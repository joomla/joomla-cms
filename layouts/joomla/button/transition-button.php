<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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

$only_icon = empty($options['transitions']);
$disabled = !empty($options['disabled']);
$tip = !empty($options['tip']);
$tipTitle = $options['tip_title'];
$checkboxName = $options['checkbox_name'];
?>
<?php if($only_icon): ?>
	<span class="<?php echo $this->escape($icon ?? ''); ?> <?php echo $tip ? 'hasPopover' : '' ?>"
		title="<?php echo HTMLHelper::_('tooltipText', Text::_($tipTitle ? : $title), '', 0) ?>"
		data-content="<?php echo HTMLHelper::_('tooltipText', Text::_($title), '', 0) ?>"
		data-placement="top"
	></span>
<?php else: ?>
	<a class="tbody-icon mr-1 data-state-<?php echo $this->escape($value ?? ''); ?> <?php echo $this->escape(!empty($disabled) ? 'disabled' : null); ?> <?php echo $tip ? 'hasPopover' : '' ?>"
		<?php if (empty($disabled)): ?>
			href="javascript://"
		<?php endif; ?>

		title="<?php echo HTMLHelper::_('tooltipText', Text::_($tipTitle ? : $title), '', 0) ?>"
		data-content="<?php echo HTMLHelper::_('tooltipText', Text::_($title), '', 0) ?>"
		data-placement="top"
		onclick="jQuery(this).nextAll().toggleClass('d-none');"
	>
		<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
	</a>
	<div class="mr-auto"><?php echo $this->escape($options['stage']); ?></div>
	<div class="d-none">
		<?php
			$attribs = [
				'id'	=> 'transition-select_' . (int) $row,
				'list.attr' => [
					'class'		=> 'custom-select custom-select-sm form-control form-control-sm',
					'onchange'		=> "Joomla.listItemTask('" . $checkboxName . $this->escape($row ?? '') . "', 'articles.runTransition')"]
				];

			echo HTMLHelper::_('select.genericlist', $options['transitions'], 'transition_' . (int) $row, $attribs);
		?>
	</div>
<?php endif; ?>
