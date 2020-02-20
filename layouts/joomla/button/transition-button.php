<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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

// get the params to decide which columns to show
use Joomla\CMS\Component\ComponentHelper;
$params = ComponentHelper::getParams('com_content');

$disabled = !empty($options['disabled']);
$tip = !empty($options['tip']);
$id = (int) $options['id'];
$tipTitle = $options['tip_title'];
$checkboxName = $options['checkbox_name'];
?>
	<a class="tbody-icon mr-1 data-state-<?php echo $this->escape($value ?? ''); ?> <?php echo $this->escape(!empty($disabled) ? 'disabled' : null); ?> <?php echo $tip ? 'hasPopover' : ''; ?>"
		<?php if (empty($disabled)) : ?>
			href="javascript://"
		<?php endif; ?>

		title="<?php echo HTMLHelper::_('tooltipText', $tipTitle ?: $title, '', 0); ?>"
		data-content="<?php echo HTMLHelper::_('tooltipText', $title, '', 0); ?>"
		data-placement="top"
		onclick="Joomla.toggleAllNextElements(this, 'd-none')"
	>
		<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
	</a>
	<?php if (($params->get('com_content_show_list_stage', 0))): ?>
		<div class="mr-auto"><?php echo $this->escape($options['stage']); ?></div>
	<?php endif; ?>
	<div class="d-none">
		<?php
			$default = [
				HTMLHelper::_('select.option', '', $this->escape($options['stage'])),
				HTMLHelper::_('select.option', '-1', '--------', ['disable' => true])
			];

			$transitions = array_merge($default, $options['transitions']);

			$attribs = [
				'id'        => 'transition-select_' . (int) $id,
				'list.attr' => [
					'class'    => 'custom-select custom-select-sm form-control form-control-sm',
					'onchange' => "Joomla.listItemTask('" . $checkboxName . $this->escape($row ?? '') . "', 'articles.runTransition')"]
				];

			echo HTMLHelper::_('select.genericlist', $transitions, 'transition_' . (int) $id, $attribs);
		?>
	</div>
