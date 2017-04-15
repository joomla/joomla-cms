<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latest
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>
<ul class="list-group list-group-flush">
	<?php if (count($list)) : ?>
		<?php foreach ($list as $i => $item) : ?>
			<li class="d-flex justify-content-start list-group-item <?php echo $item->state == 1 ? 'published' : 'unpublished'; ?>">
				<?php if ($item->checked_out) : ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time); ?>
				<?php endif; ?>
				<strong class="row-title break-word mr-2">
					<?php if ($item->link) : ?>
						<a href="<?php echo $item->link; ?>">
							<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></a>
					<?php else : ?>
						<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
					<?php endif; ?>
				</strong>
				<small class="hasTooltip" title="<?php echo JHtml::_('tooltipText', 'MOD_LATEST_CREATED_BY'); ?>">
					<?php echo $item->author_name; ?>
				</small>
				<span class="badge badge-default badge-pill ml-auto">
					<span class="small">
						<span class="icon-calendar" aria-hidden="true"></span>
						<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC5')); ?>
					</span>
				</span>
			</li>
		<?php endforeach; ?>
	<?php else : ?>
		<li class="d-flex justify-content-start list-group-item">
			<div class="col-md-12">
				<div class="alert alert-info"><?php echo JText::_('MOD_LATEST_NO_MATCHING_RESULTS');?></div>
			</div>
		</li>
	<?php endif; ?>
</ul>
