<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

HTMLHelper::_('bootstrap.popover');

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
$tip = !empty($options['tip']);
$tipTitle = $options['tip_title'];
$attr = [
	'type' => 'submit',
	'class' => 'tbody-icon data-state-' . $this->escape($value ?? ''),
	'title' => HTMLHelper::_('tooltipText', $tipTitle ?: $title, '', 0),
	'data-bs-toggle' => 'popover',
];

if ($tip)
{
	HTMLHelper::_('bootstrap.popover');

	$attr['data-bs-toggle'] = 'popover';
	$attr['data-bs-trigger'] = 'focus hover';
	$attr['data-bs-placement'] = 'top';
	$attr['data-bs-content'] = HTMLHelper::_('tooltipText', $title, '', 0);
}
if (!empty($task) && empty($disabled))
{
	$attr['onclick'] = 'return Joomla.listItemTask(\'' . $checkboxName . $this->escape($row ?? '') . '\', \'' . $this->escape(isset($task) ? $taskPrefix . $task : '') . '\')';
}

?>
<button <?php echo ArrayHelper::toString($attr); ?>>
	<span class="<?php echo $this->escape($icon ?? ''); ?>" aria-hidden="true"></span>
	<span class="sr-only"><?php echo $title; ?></span>
</button>
