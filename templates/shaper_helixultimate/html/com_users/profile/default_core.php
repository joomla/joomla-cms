<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();
?>

<div id="users-profile-core">
	<div class="d-flex mb-3">
		<div class="mr-auto">
			<strong><?php echo JText::_('COM_USERS_PROFILE_CORE_LEGEND'); ?></strong>
		</div>
		<div>
			<?php if (JFactory::getUser()->id == $this->data->id): ?>
				<a href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&user_id=' . (int) $this->data->id); ?>">
					<span class="fa fa-user"></span> <?php echo JText::_('COM_USERS_EDIT_PROFILE'); ?>
				</a>
			<?php endif;?>
		</div>
	</div>
	<ul class="list-group">
		<li class="list-group-item">
			<strong><?php echo JText::_('COM_USERS_PROFILE_NAME_LABEL'); ?></strong>:
			<?php echo $this->data->name; ?>
		</li>
		<li class="list-group-item">
			<strong><?php echo JText::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?></strong>:
			<?php echo htmlspecialchars($this->data->username, ENT_COMPAT, 'UTF-8'); ?>
		</li>
		<li class="list-group-item">
			<strong><?php echo JText::_('COM_USERS_PROFILE_REGISTERED_DATE_LABEL'); ?></strong>:
			<?php echo JHtml::_('date', $this->data->registerDate); ?>
		</li>
		<li class="list-group-item">
			<strong><?php echo JText::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?></strong>:
			<?php if ($this->data->lastvisitDate != $this->db->getNullDate()): ?>
				<?php echo JHtml::_('date', $this->data->lastvisitDate); ?>
			<?php else: ?>
				<?php echo JText::_('COM_USERS_PROFILE_NEVER_VISITED'); ?>
			<?php endif;?>
		</li>
	</ul>
</div>
