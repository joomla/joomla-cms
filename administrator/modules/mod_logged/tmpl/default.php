<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>

<div class="row-striped">
	<?php foreach ($users as $user) : ?>
		<?php
			$showLogut = false;
			if ($user->client_id === 0)
			{
				$showLogut = true;
			}

			$titleSpan = 'span6';
			if (!$showLogut)
			{
				$titleSpan = 'span7';
			}
		?>
		<div class="row-fluid">
			<dl>
				<dt class="<?php echo $titleSpan; ?>">
					<?php if (isset($user->editLink)) :?>
						<a href="<?php echo $user->editLink; ?>" rel="tooltip" title="<?php echo JText::_('JGRID_HEADING_ID');?> : <?php echo $user->id; ?>">
							<?php echo $user->name;?></a>
					<?php else :
						echo $user->name;
					endif; ?>
				</dt>

				<dd class="span2">
					<small class="small" rel="tooltip" title="<?php echo JText::_('JCLIENT'); ?>">
					<?php
						if($user->client_id) {
							echo JText::_('JADMINISTRATOR');
						} else {
							echo JText::_('JSITE');
						}?>
					</small>
				</dd>


				<?php if ($showLogut) :?>
					<dd class="span1">
						<a rel="tooltip" title="<?php echo JText::_('MOD_LOGGED_LOGOUT');?>" href="<?php echo $user->logoutLink;?>" class="btn btn-danger btn-mini">
							<i class="icon-remove icon-white tip" title="<?php echo JText::_('JLOGOUT');?>"></i>
						</a>
					</dd>
				<?php endif; ?>


				<dd class="span3">
					<span class="small" rel="tooltip" title="<?php echo JText::_('MOD_LOGGED_LAST_ACTIVITY');?>"><i class="icon-calendar"></i> <?php echo JHtml::_('date', $user->time, 'Y-m-d'); ?></span>
				</dd>
			</dl>
		</div>
	<?php endforeach; ?>
</div>
