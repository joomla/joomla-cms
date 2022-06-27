<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;

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

$disabled = !empty($options['disabled']);
$taskPrefix = $options['task_prefix'];
$checkboxName = $options['checkbox_name'];
$id = $options['id'];
$tipTitle = $options['tip_title'];

?>
<button type="submit" class="tbody-icon data-state-<?php echo $this->escape($value ?? ''); ?>"
        aria-labelledby="<?php echo $id; ?>"
        <?php echo $disabled ? 'disabled' : ''; ?>
        <?php if (!empty($task) && empty($disabled)) : ?>
            onclick="return Joomla.listItemTask('<?php echo $checkboxName . $this->escape($row ?? ''); ?>', '<?php echo $this->escape(isset($task) ? $taskPrefix . $task : ''); ?>')"
        <?php endif; ?>
>
    <span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
</button>
<div id="<?php echo $id; ?>" role="tooltip">
    <?php echo HTMLHelper::_('tooltipText', $tipTitle ?: $title, $title, 0, false); ?>
</div>
