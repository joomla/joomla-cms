<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<ul class="list-group list-group-flush">
	<?php foreach ($users as $user) : ?>
		<li class="d-flex justify-content-start list-group-item">
			<div class="fg-1">
				<?php if ($user->client_id == 0) : ?>
					<a title="<?php echo HTMLHelper::_('tooltipText', 'MOD_LOGGED_LOGOUT'); ?>" href="<?php echo $user->logoutLink; ?>" class="mr-2 btn btn-danger btn-xs hasTooltip">
						<span class="icon-remove icon-white" aria-hidden="true"><span class="sr-only"><?php echo Text::_('JLOGOUT'); ?></span></span>
					</a>
				<?php endif; ?>
				<strong class="mr-2 row-title">
					<?php if (isset($user->editLink)) : ?>
						<a href="<?php echo $user->editLink; ?>" class="hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JGRID_HEADING_ID'); ?> : <?php echo $user->id; ?>">
							<?php echo $user->name; ?></a>
					<?php else : ?>
						<?php echo $user->name; ?>
					<?php endif; ?>
				</strong>
				<small class="mr-2 small hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JCLIENT'); ?>">
					<?php if ($user->client_id === null) : ?>
						<?php // Don't display a client ?>
					<?php elseif ($user->client_id) : ?>
						<?php echo Text::_('JADMINISTRATION'); ?>
					<?php else : ?>
						<?php echo Text::_('JSITE'); ?>
					<?php endif; ?>
				</small>
			</div>
			<span class="badge badge-secondary badge-pill ml-auto hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'MOD_LOGGED_LAST_ACTIVITY'); ?>">
				<span class="small">
					<span class="icon-calendar" aria-hidden="true"></span>
					<?php echo HTMLHelper::_('date', $user->time, Text::_('DATE_FORMAT_LC5')); ?>
				</span>
			</span>
		</li>
	<?php endforeach; ?>
</ul>
