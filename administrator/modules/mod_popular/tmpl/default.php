<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<ul class="list-group list-group-flush">
	<?php if (count($list)) : ?>
		<?php foreach ($list as $i => $item) : ?>
			<?php // Calculate popular items ?>
			<?php $hits = (int) $item->hits; ?>
			<?php $hits_class = ($hits >= 10000 ? 'danger' : ($hits >= 1000 ? 'warning' : ($hits >= 100 ? 'info' : 'secondary'))); ?>
			<li class="d-flex justify-content-start list-group-item">
				<div class="fg-1">
					<span class="mr-2 badge badge-<?php echo $hits_class; ?> hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JGLOBAL_HITS'); ?>"><?php echo $item->hits; ?></span>
					<?php if ($item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time); ?>
					<?php endif; ?>
					<strong class="row-title break-word">
						<?php if ($item->link) : ?>
							<a href="<?php echo $item->link; ?>">
								<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></a>
						<?php else : ?>
							<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
						<?php endif; ?>
					</strong>
				</div>
				<span class="badge badge-secondary badge-pill">
					<span class="small">
						<span class="icon-calendar" aria-hidden="true"></span>
						<?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC5')); ?>
					</span>
				</span>
			</li>
		<?php endforeach; ?>
	<?php else : ?>
		<li class="d-flex justify-content-start list-group-item">
			<joomla-alert type="warning"><?php echo Text::_('MOD_POPULAR_NO_MATCHING_RESULTS'); ?></joomla-alert>
		</li>
	<?php endif; ?>
</ul>
