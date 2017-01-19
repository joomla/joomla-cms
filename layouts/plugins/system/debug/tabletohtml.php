<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$table       = $displayData['table'];
$hasWarnings = &$displayData['hasWarnings'];

if (empty($table))
{
	return;
}

$map       = function ($r) { return $r['Duration']; };
$filter    = function ($d) { return !is_null($d); };
$durations = array_filter(array_map($map, $table), $filter);
rsort($durations, SORT_NUMERIC);

?>

<table class="table table-striped dbg-query-table">
	<tr>
		<?php foreach (array_keys($table[0]) as $k) : ?>
			<th><?php echo htmlspecialchars($k); ?></th>
		<?php endforeach; ?>
	</tr>
	<?php foreach ($table as $tr) : ?>
		<tr>
			<?php foreach ($tr as $k => $td) : ?>
				<?php if ($td === null) $td = 'NULL'; // Display null's as 'NULL'. ?>
				<?php // Treat special columns. ?>
				<?php if ($k == 'Duration') : ?>
					<?php if ($td >= 0.001 && ($td == $durations[0] || (isset($durations[1]) && $td == $durations[1]))) : ?>
						<?php // Duration column with duration value of more than 1 ms and within 2 top duration in SQL engine: Highlight warning. ?>
						<?php $hasWarnings = true; ?>
						<td class="dbg-warning">
					<?php else : ?>
						<td>
					<?php endif; ?>
					<?php // Display duration in milliseconds with the unit instead of seconds. ?>
					<?php echo sprintf('%.2f&nbsp;ms', $td * 1000); ?>
				<?php elseif ($k == 'Error') : ?>
					<?php // An error in the EXPLAIN query occurred, display it instead of the result (means original query had syntax error most probably). ?>
					<?php $hasWarnings = true; ?>
					<td class="dbg-warning"><?php echo htmlspecialchars($td); ?>
				<?php elseif ($k == 'key') : ?>
					<?php if ($td === 'NULL') : ?>
						<?php // Displays query parts which don't use a key with warning: ?>
						<?php $hasWarnings = true; ?>
						<td>
							<strong>
								<span class="dbg-warning hasTooltip" title="<?php echo JHtml::tooltipText('PLG_DEBUG_WARNING_NO_INDEX_DESC'); ?>">
									<?php echo JText::_('PLG_DEBUG_WARNING_NO_INDEX'); ?>
								</span>
							</strong>
					<?php else : ?>
						<td><strong><?php echo htmlspecialchars($td); ?></strong>
					<?php endif; ?>
				<?php elseif ($k == 'Extra') : ?>
					<?php
					<?php // Replace spaces with &nbsp; (non-breaking spaces) for less tall tables displayed. ?>
					<?php $htmlTd = preg_replace('/([^;]) /', '\1&nbsp;', htmlspecialchars($td)); ?>
					<?php // Displays warnings for "Using filesort": ?>
					<?php $htmlTdWithWarnings = str_replace(
							'Using&nbsp;filesort',
							'<span class="dbg-warning hasTooltip" title="'
								. JHtml::tooltipText('PLG_DEBUG_WARNING_USING_FILESORT_DESC') . '">'
								. JText::_('PLG_DEBUG_WARNING_USING_FILESORT') . '</span>',
							$htmlTd
						); ?>
					<?php if ($htmlTdWithWarnings !== $htmlTd) : ?>
						<?php $hasWarnings = true; ?>
					<?php endif; ?>
					<td><?php echo $htmlTdWithWarnings; ?>
				<?php else : ?>
					<td><?php echo htmlspecialchars($td); ?>
				<?php endif; ?>
				</td>
			<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
</table>
