<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JHtml::_('bootstrap.framework');
JHtml::_('behavior.caption');

$app			= JFactory::getApplication();
$user    		= JFactory::getUser();

$params  		= $this->item->params;
$canEdit 		= $params->get('access-edit');
$avatarApp		= $this->params->get('avatar_component', 'cjforum');
$avatarSize		= $this->params->get('topic_avatar_size', 48);
$profileApp		= $this->params->get('profile_component', 'cjforum');
$layout 		= $this->params->get('layout', 'default');
$start			= $app->input->getInt('start', 0);
$theme 			= $params->get('theme', 'default');
$align	 		= $this->params->get('profile_alignment', 'left');

$api 			= new CjLibApi();
$senderLink 	= $api->getUserProfileUrl($profileApp, $this->item->sender_id, false, $this->item->sender_name);
$sentDate 		= JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2'));
$message_uri 	= JRoute::_('index.php?option=com_cjforum&view=message&id='.$this->item->id, false);
$return			= base64_encode($message_uri);

$participants = array();
foreach ($this->item->participants as $receiver)
{
	$participants[] = $api->getUserProfileUrl($profileApp, $receiver->receiver_id, false, $this->escape($receiver->receiver_name));
}
?>
<div id="cj-wrapper" class="item-page<?php echo $this->pageclass_sfx?>">
	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));?>

	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
	</div>
	<?php endif;?>
	
	<ul class="nav nav-tabs margin-bottom-5" role="tablist">
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages');?>"><?php echo JText::_('COM_CJFORUM_PMS_INBOX');?></a></li>
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages&layout=sent');?>"><?php echo JText::_('COM_CJFORUM_PMS_SENT');?></a></li>
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages&layout=trash');?>"><?php echo JText::_('COM_CJFORUM_PMS_TRASH');?></a></li>
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&task=message.add&return='.$return);?>"><?php echo JText::_('COM_CJFORUM_PMS_COMPOSE');?></a></li>
		<li class="active">
			<a href="#" onclick="return false;">
				<?php echo JText::_('COM_CJFORUM_READ_MESSAGE');?>
			</a>
		</li>
	</ul>
	
	<div class="panel panel-default">
		<div class="panel-heading"><strong><?php echo $this->escape($this->item->title);?></strong></div>
		<div class="panel-body">
			<div class="media">
				<div class="media-object pull-left">
					<?php echo $api->getUserAvatar($profileApp, $avatarApp, $this->item->created_by, $this->escape($this->item->sender_name), 48, 
						$this->item->sender_email, array('class'=>'thumbnail no-margin-bottom'));?>
				</div>
				<div class="media-body">
					<div class="muted text-muted"><?php echo JText::sprintf('COM_CJFORUM_SENT_DATE', JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC4')));?></div>
					<div class="message-body margin-top-10"><?php echo $this->item->description;?></div>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<div class="muted text-muted no-space-top"><?php echo JText::sprintf('COM_CJFORUM_MESSAGE_PARTICIPANTS', implode(', ', $participants));?></div>
		</div>
	</div>
	
	<div class="margin-bottom-10">
		<a class="btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_cjforum&task=message.add&replyto='.$this->item->id.'&return='.$return)?>">
			<?php echo JText::_('COM_CJFORUM_POST_YOUR_REPLY');?>
		</a>
	</div>
	
	<div class="panel panel-default">
		<div class="panel-heading"><strong><?php echo JText::_('COM_CJFORUM_REPLIES');?></strong></div>
		<ul class="list-group no-margin-left">
			<?php 
			foreach ($this->item->replies as $reply)
			{
				$author = $this->escape($reply->author);
				$profileUrl = $api->getUserProfileUrl($profileApp, $reply->created_by);
				$profileLink = JHtml::link($profileUrl, $author);
				$userAvatar = $api->getUserAvatarImage($avatarApp, $reply->created_by, $reply->author_email, 24, false, $author);
				?>
				<li class="list-group-item pad-bottom-5" id="p<?php echo $reply->id;?>">
					<div class="reply-title">
						<a class="muted text-muted" href="<?php echo JRoute::_($message_uri.'#p'.$reply->id);?>">
							<?php echo JText::sprintf('COM_CJFORUM_REPLY_HEADER', $this->item->title, array('jsSafe'=>true))?>
						</a>
					</div>
					<p class="author small">
						<?php echo JText::sprintf('COM_CJFORUM_TOPIC_REPLY_AUTHOR_NAME', $profileLink);?>
						&raquo; <?php echo CjLibDateUtils::getHumanReadableDate($reply->created);?>
					</p>
					
					<hr class="no-margin-top no-margin-bottom">
					
					<div class="media clearfix">
						<div class="pull-<?php echo $align;?> avatar hidden-phone">
							<a href="<?php echo $profileUrl;?>" class="thumbnail margin-bottom-5" title="<?php echo $this->escape($reply->author);?>" data-toggle="tooltip">
								<?php echo $api->getUserAvatarImage($avatarApp, $reply->created_by, $reply->author_email, $avatarSize, false, $this->escape($reply->author));?>
							</a>
							<div class="center text-center">
								<a href="<?php echo $profileUrl;?>"><?php echo $this->escape($reply->author);?></a>
							</div>
							<div class="center text-center user_rank_image">
								<?php echo CjForumApi::getUserRankImage($reply->created_by, $this->params->get('rank_profile', 'default'));?>
							</div>
						</div>
						
						<div class="media-body">
							<div class="reply-description"><?php echo $reply->description;?></div>
							<div class="user-signature"></div>
						</div>
					</div>
					
					<?php if($this->item->params->get('access-edit') || $this->item->params->get('access-edit-state')):?>
					<hr class="margin-top-10 margin-bottom-10">
					<div class="btn-group">
						<?php if($this->item->params->get('access-edit')):?>
						<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" href="#" onclick="document.adminForm.cid.value=<?php echo $reply->id;?>;Joomla.submitbutton('message.edit'); return false;">
							<i class="fa fa-edit"></i> <?php echo JText::_('JGLOBAL_EDIT');?>
						</a>
						<?php endif;?>
						
						<?php if($this->item->params->get('access-edit-state')):?>
							<?php if($reply->state == 1):?>
							<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" href="#" onclick="document.adminForm.cid.value=<?php echo $reply->id;?>;Joomla.submitbutton('messages.unpublish'); return false;">
								<i class="fa fa-ban"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_UNPUBLISH');?>
							</a>
							<?php else:?>
							<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" href="#" onclick="document.adminForm.cid.value=<?php echo $reply->id;?>;Joomla.submitbutton('messages.publish'); return false;">
								<i class="fa fa-check-square"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_PUBLISH');?>
							</a>
							<?php endif;?>
							
							<?php if($reply->state != -2):?>
							<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" href="#" onclick="document.adminForm.cid.value=<?php echo $reply->id;?>;Joomla.submitbutton('messages.trash'); return false;">
								<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_TRASH');?>
							</a>
							<?php else:?>
							<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" href="#" onclick="document.adminForm.cid.value=<?php echo $reply->id;?>;Joomla.submitbutton('messages.delete'); return false;">
								<i class="fa fa-times-circle"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_PERMANANTLY_DELETE');?>
							</a>
							<?php endif;?>
						<?php endif;?>
					</div>
					
					<form action="<?php echo JRoute::_('index.php?option=com_cjforum'); ?>" name="adminForm" id="adminForm" method="post" style="display: none;">
						<input type="hidden" name="cid" value="">
						<input type="hidden" name="view" value="messages">
						<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_($message_uri));?>">
						<?php echo JHtml::_('form.token'); ?>
					</form>
					<?php endif;?>
				</li>
			<?php 
			}
			?>
		</ul>
	</div>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
</div>