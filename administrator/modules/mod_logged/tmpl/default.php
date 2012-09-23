<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
?>
<div class="row-striped">
	<?php foreach ($users as $user) : ?>
		<div class="row-fluid">
			<div class="span9">
				<?php if ($user->client_id == 0) :?>
					<a class="hasTooltip" title="<?php echo JText::_('MOD_LOGGED_LOGOUT');?>" href="<?php echo $user->logoutLink;?>" class="btn btn-danger btn-mini">
						<i class="icon-remove icon-white tip" title="<?php echo JText::_('JLOGOUT');?>"></i>
					</a>
				<?php endif; ?>

				<strong class="row-title">
					<?php if (isset($user->editLink)) :?>
						<a href="<?php echo $user->editLink; ?>" class="hasTooltip" title="<?php echo JText::_('JGRID_HEADING_ID');?> : <?php echo $user->id; ?>">
							<?php echo $user->name;?></a>
					<?php else :
						echo $user->name;
					endif; ?>
				</strong>

				<small class="small hasTooltip" title="<?php echo JText::_('JCLIENT'); ?>">
				<?php
					if($user->client_id) {
						echo JText::_('JADMINISTRATOR');
					} else {
						echo JText::_('JSITE');
					}?>
				</small>
			</div>
			<div class="span3">
				<span class="small hasTooltip" title="<?php echo JText::_('MOD_LOGGED_LAST_ACTIVITY');?>"><i class="icon-calendar"></i> <?php echo JHtml::_('date', $user->time, 'Y-m-d'); ?></span>
			</div>
		</div>
	<?php endforeach; ?>
</div>
