<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	mod_popular
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<table class="adminlist" summary="<?php echo JText::_('MOD_POPULAR_TABLE_SUMMARY'); ?>">
	<thead>
		<tr>
			<th>
				<?php echo JText::_('Most Popular Items'); ?>
			</th>
			<th>
				<?php echo JText::_('Created'); ?>
			</th>
			<th>
				<?php echo JText::_('Hits'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($rows as $row) : ?>
		<tr>
			<td>
				<a href="<?php echo JRoute::_('index.php?option=com_content&amp;task=edit&amp;id='.(int) $row->id); ?>">
					<?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');?></a>
			</td>
			<td class="center">
				<?php echo JHtml::_('date', $row->created, '%Y-%m-%d %H:%M:%S'); ?>
			</td>
			<td class="center">
				<?php echo $row->hits;?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>