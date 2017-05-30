<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$backtrace = $displayData['backtrace'];

if (!is_array($backtrace))
{
	return;
}

$backtrace = array_reverse($backtrace);
?>

<table cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="3"><strong>Call stack</strong></td>
	</tr>
	<tr>
		<th>#</th>
		<th>Function</th>
		<th>Location</th>
	</tr>
	<?php foreach ($backtrace as $i => $entry) : ?>
		<?php $link = isset($entry['file']) ? JDebugHelper::formatLink($entry['file'], $entry['line']) : '&#160;'; ?>
		<tr>
			<td><?php echo $i + 1; ?></td>
			<?php if (isset($entry['class'])) : ?>
				<td><?php echo $entry['class'], $entry['type'], $entry['function']; ?>()</td>
			<?php else : ?>
				<td><?php echo $entry['function']; ?>()</td>';
			<?php endif; ?>
			<td><?php echo $link; ?></td>
		</tr>
	<?php endforeach; ?>
</table>
