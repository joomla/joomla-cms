<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData, EXTR_OVERWRITE);

/**
 * Layout variables
 * -----------------
 * @var   string  $icon
 * @var   string  $title
 * @var   string  $value
 * @var   string  $task
 * @var   array   $options
 */

$only_icon = empty($options['transitions']);
$disabled = !empty($options['disabled']);
$tip = !empty($options['tip']);
$tipTitle = $options['tip_title'];
$tipContent = $options['tip_content'];
$checkboxName = $options['checkbox_name'];

if ($tip)
{
	HTMLHelper::_('bootstrap.popover', '.hasPopover', ['trigger' => 'hover focus']);
}
?>
<?php if ($only_icon || $disabled) : ?>
	<span class="tbody-icon me-1 align-self-start <?php echo $tip ? 'hasPopover' : ''; ?> disabled"
			title="<?php echo HTMLHelper::_('tooltipText', $tipTitle ?: $title, '', 0); ?>"
			data-bs-content="<?php echo HTMLHelper::_('tooltipText', $tipContent, '', 0); ?>"
			data-bs-placement="top"
		>
		<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
	</span>
	<div class="me-auto">
		<?php echo $options['title']; ?>
		<?php if ($tipContent) : ?>
		<span class="visually-hidden"><?php echo $tipContent; ?></span>
		<?php endif; ?>
	</div>
<?php else : ?>
	<?php HTMLHelper::_('bootstrap.popover', '.hasPopover', ['trigger' => 'hover focus']); ?>
	<button type="button" class="tbody-icon align-self-start me-1 data-state-<?php echo $this->escape($value ?? ''); ?> <?php echo $tip ? 'hasPopover' : ''; ?>"
		title="<?php echo HTMLHelper::_('tooltipText', $tipTitle ?: $title, '', 0); ?>"
		data-bs-content="<?php echo HTMLHelper::_('tooltipText', $tipContent, '', 0); ?>"
		data-bs-placement="top"
		onclick="Joomla.toggleAllNextElements(this, 'd-none')"
	>
		<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
		<span class="visually-hidden"><?php echo Text::_('JWORKFLOW_SHOW_TRANSITIONS_FOR_THIS_ITEM'); ?></span>
	</button>
	<div class="me-auto">
		<?php echo $options['title']; ?>
		<?php if ($tipContent) : ?>
		<span class="visually-hidden"><?php echo $tipContent; ?></span>
		<?php endif; ?>
	</div>
	<div class="d-none">
		<span class="visually-hidden">
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
					'class'    => 'form-select form-select-sm w-auto',
					'onchange' => "this.form.transition_id.value=this.value;Joomla.listItemTask('" . $checkboxName . $this->escape($row ?? '') . "', 'articles.runTransition')"]
				];

			echo HTMLHelper::_('select.genericlist', $transitions, '', $attribs);
		?>
	</div>
<?php endif; ?>
