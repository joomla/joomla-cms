<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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

Factory::getDocument()->getWebAssetManager()->useScript('list-view');

$disabled = !empty($options['disabled']);
$taskPrefix = $options['task_prefix'];
$checkboxName = $options['checkbox_name'];
$id = $options['id'];
$tipTitle = $options['tip_title'];

?>
<button type="button"
    class="js-grid-item-action tbody-icon data-state-<?php echo $this->escape($value ?? ''); ?>"
    aria-labelledby="<?php echo $id; ?>"
    <?php echo $disabled ? 'disabled' : ''; ?>
    data-item-id="<?php echo $checkboxName . $this->escape($row ?? ''); ?>"
    data-item-task="<?php echo $this->escape(isset($task) ? $taskPrefix . $task : ''); ?>"
>
    <span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
</button>
<div id="<?php echo $id; ?>" role="tooltip">
    <?php echo HTMLHelper::_('tooltipText', $tipTitle ?: $title, $title, 0, false); ?>
</div>
