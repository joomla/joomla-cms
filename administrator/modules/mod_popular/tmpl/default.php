<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
?>
<div class="row-striped">
	<?php if (count($list)) : ?>
		<?php foreach ($list as $i => $item) : ?>
			<?php // Calculate popular items ?>
			<?php $hits = (int) $item->hits; ?>
			<?php $hits_class = $hits >= 100 ? 'important' : $hits >= 25 ? 'warning' : $hits >= 10 ? 'info' : ''; ?>
			<div class="row-fluid">
				<div class="span9">
					<span class="badge badge-<?php echo $hits_class; ?> hasTooltip" title="<?php echo JHtml::tooltipText('JGLOBAL_HITS'); ?>"><?php echo $item->hits; ?></span>
					<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time); ?>
					<?php endif; ?>

					<strong class="row-title">
						<?php if ($item->link) : ?>
							<a href="<?php echo $item->link; ?>">
								<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></a>
						<?php else : ?>
							<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
						<?php endif; ?>
					</strong>
				</div>
				<div class="span3">
					<span class="small">
						<i class="icon-calendar"></i>
						<?php echo JHtml::_('date', $item->created, 'Y-m-d'); ?>
					</span>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<div class="row-fluid">
			<div class="span12">
				<div class="alert"><?php echo JText::_('MOD_POPULAR_NO_MATCHING_RESULTS'); ?></div>
			</div>
		</div>
	<?php endif; ?>
</div>
