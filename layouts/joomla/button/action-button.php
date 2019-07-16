<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

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
?>
<button type="submit" class="tbody-icon data-state-<?php echo $this->escape($value ?? ''); ?>" 
	<?php echo !empty($disabled) ? 'disabled' : ''; ?>
	<?php if (!empty($task) && empty($disabled)) : ?>
		onclick="return Joomla.listItemTask('<?php echo $checkboxName . $this->escape($row ?? ''); ?>', '<?php echo $this->escape(isset($task) ? $taskPrefix . $task : ''); ?>')"
	<?php endif; ?>
>
	<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
	<span class="sr-only"><?php echo Text::_($title); ?></span>
</button>
