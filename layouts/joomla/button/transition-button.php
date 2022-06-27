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

$disabled = empty($options['transitions']) || !empty($options['disabled']);
$id = $options['id'];
$tipTitle = $options['tip_title'];
$tipContent = $options['tip_content'];
$checkboxName = $options['checkbox_name'];
$task = $options['task'];

?>
<button type="button" class="tbody-icon data-state-<?php echo $this->escape($value ?? ''); ?>"
        aria-labelledby="<?php echo $id; ?>"
        <?php echo $disabled ? 'disabled' : ''; ?>
        <?php if (!$disabled) : ?>
            onclick="Joomla.toggleAllNextElements(this, 'd-none')"
        <?php endif; ?>
    >
    <span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
</button>
<div id="<?php echo $id; ?>" role="tooltip">
    <?php echo HTMLHelper::_('tooltipText', $tipTitle ?: $title, $tipContent, 0, false); ?>
</div>

<?php if (!$disabled) : ?>
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
                    'onchange' => "this.form.transition_id.value=this.value;Joomla.listItemTask('" . $checkboxName . $this->escape($row ?? '') . "', '" . $task . "')"]
                ];

            echo HTMLHelper::_('select.genericlist', $transitions, '', $attribs);
            ?>
    </div>
<?php endif; ?>
