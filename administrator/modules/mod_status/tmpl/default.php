<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<ul class="inline">
<?php if ($params->get('show_loggedin_users_admin', 1)) : ?>
	<li class="backloggedin-users">
		<span class="badge"><?php echo $count; ?></span> 
		<?php echo JText::plural('MOD_STATUS_BACKEND_USERS', $count); ?>
	</li>
<?php endif; ?>

<?php if ($params->get('show_loggedin_users', 1)) : ?>
	<li class="loggedin-users">
		<span class="badge"><?php echo $online_num; ?></span>
		<?php echo JText::plural('MOD_STATUS_USERS', $online_num); ?>
	</li>
<?php endif; ?>

<?php if ($params->get('show_messages', 1)) : ?>
	<?php $active = $unread ? ' badge-warning' : ''; ?>
	<li class="messages <?php echo $inboxClass; ?>">
		<?php if ($hideLinks) : ?>
			<span class="badge <?php echo $active; ?>"><?php echo $unread; ?></span>
			<?php echo JText::plural('MOD_STATUS_MESSAGES', $unread); ?>
		<?php else : ?>
			<span class="badge <?php echo $active; ?>"><?php echo $unread; ?></span>
			<a href="<?php echo $inboxLink; ?>">
				<?php echo JText::plural('MOD_STATUS_MESSAGES', $unread); ?>
			</a>
		<?php endif; ?>
	</li>
<?php endif; ?>

<li class="divider"></li>

<?php if ($params->get('show_viewsite', 1)) : ?>
	<li class="viewsite">
		<a href="<?php echo JUri::root(); ?>" target="_blank">
			<i class="icon-out-2"></i><?php echo JText::_('JGLOBAL_VIEW_SITE'); ?>
		</a>
	</li>
<?php endif; ?>

<?php if ($params->get('show_logout', 1)) : ?>
	<li class="logout">
		<?php if($hideLinks) : ?>
			<i class="icon-exit"></i><?php echo JText::_('JLOGOUT'); ?>
		<?php else : ?>
			<a href="<?php echo $logoutLink; ?>">
				<i class="icon-exit"></i><?php echo JText::_('JLOGOUT'); ?>
			</a>
		<?php endif; ?>
	</li>
<?php endif; ?>
</ul>