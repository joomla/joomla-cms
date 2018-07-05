<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latest
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<table class="table table-striped" id="<?php echo str_replace(' ', '', $module->title) . $module->id; ?>">
	<?php if (!$module->showtitle) : ?>
		<caption class="sr-only"><?php echo $module->title; ?></caption>
	<?php endif; ?>
	<thead>
		<tr>
			<th scope="col" style="width:60%"><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
			<th scope="col" style="width:20%"><?php echo Text::_('JAUTHOR'); ?></th>
			<th scope="col" style="width:20%"><?php echo Text::_('JDATE'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if (count($list)) : ?>
		<?php foreach ($list as $i => $item) : ?>
		<tr>
			<th scope="row">
				<?php if ($item->checked_out) : ?>
					<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time); ?>
				<?php endif; ?>
				<?php if ($item->link) : ?>
					<a href="<?php echo $item->link; ?>">
						<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span><?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
					</a>
				<?php else : ?>
					<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
				<?php endif; ?>
			</th>
			<td>
				<?php echo $item->author_name; ?>
			</td>
			<td>
				<span class="badge badge-secondary badge-pill">
					<span class="small">
						<span class="icon-calendar" aria-hidden="true"></span>
						<?php echo HTMLHelper::_('date', $item->publish_up, Text::_('DATE_FORMAT_LC4')); ?>
					</span>
				</span>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php else : ?>
		<tr>
			<td colspan="3">
				<?php echo Text::_('MOD_LATEST_NO_MATCHING_RESULTS'); ?>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>