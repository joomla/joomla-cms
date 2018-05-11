<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latest
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<ul class="list-group list-group-flush">
	<?php if (count($list)) : ?>
		<?php foreach ($list as $i => $item) : ?>
			<li class="d-flex justify-content-start list-group-item <?php echo $item->state_condition == '1' ? 'published' : 'unpublished'; ?>">
				<div class="fg-1">
					<?php if ($item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time); ?>
					<?php endif; ?>
					<strong class="row-title break-word mr-2" title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
						<?php if ($item->link) : ?>
							<a href="<?php echo $item->link; ?>">
								<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></a>
						<?php else : ?>
							<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
						<?php endif; ?>
					</strong>
					<small class="hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'MOD_LATEST_CREATED_BY'); ?>">
						<?php echo $item->author_name; ?>
					</small>
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
			<joomla-alert type="warning"><?php echo Text::_('MOD_LATEST_NO_MATCHING_RESULTS'); ?></joomla-alert>
		</li>
	<?php endif; ?>
</ul>
