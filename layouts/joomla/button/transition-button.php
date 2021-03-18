<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
$tipContent = $options['tip_content'];
$checkboxName = $options['checkbox_name'];
?>
<?php if ($only_icon || $disabled) : ?>
	<span class="tbody-icon mr-1 align-self-start <?php echo $tip ? 'hasPopover' : ''; ?> disabled"
			title="<?php echo HTMLHelper::_('tooltipText', $tipTitle ?: $title, '', 0); ?>"
			data-content="<?php echo HTMLHelper::_('tooltipText', $tipContent, '', 0); ?>"
			data-placement="top"
		>
		<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
	</span>
	<div class="mr-auto">
		<?php echo $options['title']; ?>
		<?php if ($tipContent) : ?>
		<span class="sr-only"><?php echo $tipContent; ?></span>
		<?php endif; ?>
	</div>
<?php else : ?>
	<button type="button" class="tbody-icon align-self-start mr-1 data-state-<?php echo $this->escape($value ?? ''); ?> <?php echo $tip ? 'hasPopover' : ''; ?>"
		title="<?php echo HTMLHelper::_('tooltipText', $tipTitle ?: $title, '', 0); ?>"
		data-content="<?php echo HTMLHelper::_('tooltipText', $tipContent, '', 0); ?>"
		data-placement="top"
		onclick="Joomla.toggleAllNextElements(this, 'd-none')"
	>
		<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
		<span class="sr-only"><?php echo Text::_('JWORKFLOW_SHOW_TRANSITIONS_FOR_THIS_ITEM'); ?></span>
	</button>
	<div class="mr-auto">
		<?php echo $options['title']; ?>
		<?php if ($tipContent) : ?>
		<span class="sr-only"><?php echo $tipContent; ?></span>
		<?php endif; ?>
	</div>
	<div class="d-none">
		<span class="sr-only">
			<label for="transition-select_<?php echo (int) $row ?? ''; ?>">
			<?php echo Text::_('JWORKFLOW_EXECUTE_TRANSITION'); ?>
			</label>
		</span>
		<?php
			$default = [
				HTMLHelper::_('select.option', '', $this->escape($options['title'])),
				HTMLHelper::_('select.option', '-1', '--------', ['disable' => true])
			];

			$transitions = array_merge($default, $options['transitions']);

			$attribs = [
				'id'        => 'transition-select_' . (int) $row ?? '',
				'list.attr' => [
					'class'    => 'custom-select custom-select-sm w-auto',
					'onchange' => "this.form.transition_id.value=this.value;Joomla.listItemTask('" . $checkboxName . $this->escape($row ?? '') . "', 'articles.runTransition')"]
				];

			echo HTMLHelper::_('select.genericlist', $transitions, '', $attribs);
		?>
	</div>
<?php endif; ?>
