<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * ------------------
 * 	stats                   : (array) Array containing the layout statistics
 * 	  - fileSearches        : (int) number of layout searches performed
 * 	  - fileSearchesCached  : (int) number of searches that were already cached
 * 	  - fileSearchesSkipped : (int) numer of searches skipped because they have been done within the same instance
 * 	  - times               : (array) array containing the times spent globally and per layout (key = layout path)
 * 	      - fileSearching   : (float) time spent searching layouts on the filesystem
 * 	      - fileRendering   : (float) time spent rendering the layouts
 * 	  - timeFileSearching   : (float) time spent searching layouts on filesystem
 * 	  - layoutsRendered     : (array) list of arrays rendered in the current page
 */
?>
<ul>
	<li>
		<strong><?php echo JText::_('PLG_DEBUG_LAYOUTS_FILE_SEARCHES'); ?>: </strong>
		<span><?php echo $stats['fileSearches']; ?></span> |
		<strong><?php echo JText::_('PLG_DEBUG_LAYOUTS_FILE_SEARCHES_SKIPPED'); ?>: </strong>
		<span><?php echo $stats['fileSearchesSkipped']; ?></span> |
		<strong><?php echo JText::_('PLG_DEBUG_LAYOUTS_FILE_SEARCHES_CACHED'); ?>: </strong>
		<span><?php echo $stats['fileSearchesCached']; ?></span>
	</li>
	<li>
		<strong><?php echo JText::_('PLG_DEBUG_LAYOUTS_FILE_SEARCHING_TIME'); ?>: </strong>
		<span><?php echo number_format($stats['times']['fileSearching'], 6); ?></span>
	</li>
	<li>
		<strong><?php echo JText::_('PLG_DEBUG_LAYOUTS_FILE_RENDERING_TIME'); ?>: </strong>
		<span><?php echo number_format($stats['times']['fileRendering'], 6); ?></span>
	</li>
	<li>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th><?php echo JText::_('PLG_DEBUG_LAYOUT') . ' (' . count($stats['layoutsRendered']) . ')'; ?></th>
					<th><?php echo JText::_('PLG_DEBUG_LAYOUT_RENDERED_TIMES'); ?></th>
					<th><?php echo JText::_('PLG_DEBUG_LAYOUT_RENDERING_TIME'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ($stats['layoutsRendered']) : ?>
					<?php foreach ($stats['layoutsRendered'] as $layout => $timesRendered) : ?>
						<tr>
							<td><?php echo $layout; ?></td>
							<td><?php echo $timesRendered; ?></td>
							<td><?php echo number_format($stats['times'][$layout], 6); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</li>
</ul>
