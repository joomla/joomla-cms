<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
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
			<th scope="col" style="width:50%">
				<?php if ($params->get('name', 1) == 0) : ?>
					<?php echo Text::_('JGLOBAL_USERNAME'); ?>
				<?php else : ?>
					<?php echo Text::_('MOD_LOGGED_NAME'); ?>
				<?php endif; ?>
			</th>
			<th scope="col" style="width:30%"><?php echo Text::_('JCLIENT'); ?></th>
			<th scope="col" style="width:20%"><?php echo Text::_('MOD_LOGGED_LAST_ACTIVITY'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($users as $user) : ?>
		<tr>
			<th scope="row">
				<?php if (isset($user->editLink)) : ?>
					<a href="<?php echo $user->editLink; ?>">
						<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span><?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?>
					</a>
				<?php else : ?>
					<?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?>
				<?php endif; ?>
			</th>
			<td>
				<?php if ($user->client_id === null) : ?>
					<?php // Don't display a client ?>
				<?php elseif ($user->client_id) : ?>
					<?php echo Text::_('JADMINISTRATION'); ?>
				<?php else : ?>
					<?php echo Text::_('JSITE'); ?>
					<a href="<?php echo $user->logoutLink; ?>" class="mr-2 btn btn-danger btn-xs" role="button">
						<span class="icon-remove icon-white" aria-hidden="true"></span>
						<?php echo Text::_('JLOGOUT'); ?>
					</a>
				<?php endif; ?>
			</td>
			<td>
				<span class="badge badge-secondary badge-pill">
					<span class="small">
						<span class="icon-calendar" aria-hidden="true"></span>
						<?php echo HTMLHelper::_('date', $user->time, Text::_('DATE_FORMAT_LC5')); ?>
					</span>
				</span>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
