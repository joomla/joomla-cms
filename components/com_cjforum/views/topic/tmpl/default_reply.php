<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$app				= JFactory::getApplication();
$user    			= JFactory::getUser();
$api				= new CjLibApi();

$microdata 			= new JMicrodata('Comment');
$userComments		= new JMicrodata('commentText');
$params  			= $this->item->params;
$canEdit 			= $params->get('access-edit');
$info    			= $params->get('info_block_position', 0);
$avatarApp			= $params->get('avatar_component', 'cjforum');
$avatarSize			= $params->get('topic_avatar_size', 96);
$profileApp			= $params->get('profile_component', 'cjforum');
$pointsApp			= $params->get('points_component', 'none');
$align	 			= $params->get('profile_alignment', 'left');
$layout 			= $params->get('layout', 'default');
$start				= $app->input->getInt('start', 0);
$topic_uri			= CjForumHelperRoute::getTopicRoute($this->item->slug, $this->item->catslug, $this->item->language, $start);
$theme 				= $params->get('theme', 'default');

$profileApi			= CjForumApi::getProfileApi();
$profile 			= $profileApi->getUserProfile($this->reply->created_by);
$author_url 		= $api->getUserProfileUrl($profileApp, $this->reply->created_by);
$author_name 		= $microdata->content($this->reply->author)->property('name')->fallback('Person', 'name')->display();
$author_link		= JHtml::link($author_url, $author_name, array('itemprop'=>'url'));
$found 				= false;

foreach ($this->likes as $like)
{
	if($like->item_type == ITEM_TYPE_REPLY && $like->item_id == $this->reply->id)
	{
		$found = $like->action_value;
		break;
	}
}

$thankyou = array();
$isaidthankyou = false;

foreach ($this->thankyou as $thank)
{
	if($thank->item_type == 2 && $thank->item_id == $this->reply->id)
	{
		$thankyou[] = $api->getUserProfileUrl($profileApp, $thank->created_by, false, $thank->created_by_name);
		
		if(!$user->guest && $thank->created_by == $user->id && $this->reply->created_by != $user->id)
		{
			$isaidthankyou = true;
		}
	}
}
?>

<p class="reply-title">
	<a class="muted text-muted" href="<?php echo JRoute::_($topic_uri.'#p'.$this->reply->id);?>">
		<?php echo JText::sprintf('COM_CJFORUM_REPLY_HEADER', $this->item->title, array('jsSafe'=>true))?>
	</a>
</p>
<p class="author small">
	<span <?php echo $microdata->htmlProperty('creator');?>><?php echo JText::sprintf('COM_CJFORUM_TOPIC_REPLY_AUTHOR_NAME', $author_link);?></span>
	&raquo; <span <?php echo $microdata->property('commentTime')->fallback('Date', 'dateCreated')->display();?>><?php echo CjLibDateUtils::getHumanReadableDate($this->reply->created);?></span>
</p>

<hr class="no-margin-top no-margin-bottom">

<div class="media clearfix">
	<div class="pull-<?php echo $align;?> avatar hidden-phone hidden-xs">
		<?php if($avatarApp != 'none'):?>
		<a href="<?php echo $author_url;?>" class="thumbnail margin-bottom-5" title="<?php echo $this->escape($this->reply->author);?>" data-toggle="tooltip">
			<img src="<?php echo $api->getUserAvatarImage($avatarApp, $this->reply->created_by, $this->reply->author_email, $avatarSize, true);?>" 
				alt="<?php echo $this->escape($this->reply->author);?>" class="media-object no-space-left no-space-right" style="width: 100%;">
		</a>
		<?php endif;?>
		<div class="center text-center">
			<a href="<?php echo $author_url;?>"><?php echo $this->escape($this->reply->author);?></a>
		</div>
		<div class="center text-center user_rank_image margin-bottom-5">
			<?php echo CjForumApi::getUserRankImage($this->reply->created_by, $params->get('rank_profile', 'default'));?>
		</div>
		<div class="center text-center">
			<span class="label label-info margin-right-2" title="<?php echo JText::_('COM_CJFORUM_TOPICS');?>" data-toggle="tooltip">
				<?php echo CjLibUtils::formatNumber($profile['topics']);?>
			</span>
			<span class="label label-warning margin-right-2" title="<?php echo JText::_('COM_CJFORUM_REPLIES');?>" data-toggle="tooltip">
				<?php echo CjLibUtils::formatNumber($profile['replies']);?>
			</span>
			<?php if($pointsApp != 'none'):?>
			<span class="label label-success" title="<?php echo JText::_('COM_CJFORUM_POINTS');?>" data-toggle="tooltip">
				<?php echo CjLibUtils::formatNumber($profile['points']);?>
			</span>
			<?php endif;?>
		</div>
		<div class="center text-center margin-top-10">
			<?php if(!empty($profile['twitter'])):?>
			<a href="https://twitter.com/<?php echo $this->escape($profile['twitter']);?>" title="Twitter" data-toggle="tooltip" target="_blank">
				<i class="fa fa-twitter fa-border"></i>
			</a>
			<?php endif;?>
			<?php if(!empty($profile['facebook'])):?>
			<a href="https://www.facebook.com/<?php echo $this->escape($profile['facebook']);?>" title="Facebook" data-toggle="tooltip" target="_blank">
				<i class="fa fa-facebook fa-border"></i>
			</a>
			<?php endif;?>
			<?php if(!empty($profile['gplus'])):?>
			<a href="https://plus.google.com/<?php echo $this->escape($profile['gplus']);?>" title="Google+" data-toggle="tooltip" target="_blank">
				<i class="fa fa-google-plus fa-border"></i>
			</a>
			<?php endif;?>
			<?php if(!empty($profile['linkedin'])):?>
			<a href="https://www.linkedin.com/profile/view?id=<?php echo $this->escape($profile['linkedin']);?>" title="Linkedin" data-toggle="tooltip" target="_blank">
				<i class="fa fa-linkedin fa-border"></i>
			</a>
			<?php endif;?>
			<?php if(!empty($profile['flickr'])):?>
			<a href="https://www.flickr.com/photos/<?php echo $this->escape($profile['flickr']);?>" title="Flickr" data-toggle="tooltip" target="_blank">
				<i class="fa fa-flickr fa-border"></i>
			</a>
			<?php endif;?>
			<?php if(!empty($profile['skype'])):?>
			<a href="skype:<?php echo $this->escape($profile['skype']);?>" title="Skype" data-toggle="tooltip">
				<i class="fa fa-skype fa-border"></i>
			</a>
			<?php endif;?>
		</div>
	</div>
	<div class="media-body">
		<div class="reply-description"><?php echo $userComments->content($this->reply->text)->property('commentText')->fallback('Thing', 'description')->display();?></div>
		<?php
		if(!empty($this->reply->attachments))
		{
			echo JLayoutHelper::render($layout.'.attachments', array('item'=>$this->reply, 'params'=>$this->item->params));
		}
		?>
		
		<?php if(!empty($profile['signature'])):?>
		<div class="user-signature text-muted">
			<hr/>
			<?php echo $profile['signature'];?>
		</div>
		<?php endif;?>
	</div>
</div>

<hr class="margin-bottom-10">

<?php if(!empty($thankyou)):?>
<div>
	<?php echo JText::sprintf('COM_CJFORUM_USERS_SAID_THANKYOU', implode(', ', $thankyou));?>
</div>
<hr class="margin-bottom-10 margin-top-10">
<?php endif;?>

<div class="topic-actions clearfix">
	<div class="btn-toolbar pull-right no-margin-bottom no-margin-top">
	
		<?php if($this->item->locked == 0 && $this->item->params->get('access-reply')):?>
		<div class="btn-group">
			<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" href="#" 
				onclick="document.adminForm.quote.value=<?php echo $this->reply->id;?>;document.adminForm.cid.value=<?php echo $this->reply->id;?>;Joomla.submitbutton('reply.add'); return false;">
				<i class="fa fa-quote-left"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_QUOTE');?>
			</a>
			<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" href="#" onclick="Joomla.submitbutton('reply.add'); return false;">
				<i class="fa fa-reply"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_REPLY');?>
			</a>
			<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" href="#adminForm" onclick="return false;">
				<i class="fa fa-reply"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_QUICK_REPLY');?>
			</a>
		</div>
		<?php endif;?>
		
		<?php if($this->reply->params->get('access-edit') || $this->reply->params->get('access-edit-state')):?>
		<div class="btn-group">
			<a class="btn btn-mini btn-xs btn-<?php echo $theme;?> dropdown-toggle" data-toggle="dropdown" href="#">
				<?php echo JText::_('COM_CJFORUM_TOPIC_ACTIONS');?> <span class="fa fa-caret-down"></span>
			</a>
			<ul class="dropdown-menu pull-right" role="menu">
				<?php if($this->reply->params->get('access-edit')):?>
				<li>
					<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->reply->id;?>;Joomla.submitbutton('reply.edit'); return false;">
						<i class="fa fa-edit"></i> <?php echo JText::_('JGLOBAL_EDIT');?>
					</a>
				</li>
				<?php endif;?>
				
				<?php if($this->reply->params->get('access-edit-state')):?>
				<li>
					<?php if($this->reply->state == 1):?>
					<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->reply->id;?>;Joomla.submitbutton('replies.unpublish'); return false;">
						<i class="fa fa-ban"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_UNPUBLISH');?>
					</a>
					<?php else:?>
					<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->reply->id;?>;Joomla.submitbutton('replies.publish'); return false;">
						<i class="fa fa-check-square"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_PUBLISH');?>
					</a>
					<?php endif;?>
				</li>
				<li>
					<?php if($this->reply->state != -2):?>
					<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->reply->id;?>;Joomla.submitbutton('replies.trash'); return false;">
						<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_TRASH');?>
					</a>
					<?php else:?>
					<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->reply->id;?>;Joomla.submitbutton('replies.delete'); return false;">
						<i class="fa fa-times-circle"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_PERMANANTLY_DELETE');?>
					</a>
					<?php endif;?>
				</li>
				<?php endif;?>
			</ul>
		</div>
		<?php endif;?>
	</div>
	<div class="btn-toolbar no-margin-bottom no-margin-top">
		<div class="btn-group">
			<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" href="<?php echo JRoute::_($topic_uri.'#p'.$this->reply->id);?>" 
				title="<?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_PERMALINK');?>" data-toggle="tooltip">
				&nbsp;<i class="fa fa-magnet"></i>&nbsp;
			</a>
		</div>
		
		<?php 
		if($params->get('enable_ratings', true))
		{
			if(!$user->guest && $user->authorise('core.vote', 'com_cjforum.topic.'.$this->item->id))
			{
				?>
				<div class="btn-group user-ratings">
					<a class="btn btn-mini btn-xs btn-<?php echo $theme;?><?php echo ($found !== false && $found == 2)  ? ' btn-danger' : '';?>" href="#" 
						title="<?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_DISLIKE');?>" data-toggle="tooltip" 
						onclick="document.adminForm.cid.value=<?php echo $this->reply->id?>;CjForumApi.submitAjaxForm(this, '#adminForm', 'rating.rdislike', 'onBeforeLike', 'onAfterLike');return false;">
						<i class="fa fa-thumbs-o-down"></i> <span class="user-rating"><?php echo JText::plural('COM_CJFORUM_NUM_DISLIKES', $this->reply->dislikes);?></span>
					</a>
					
					<a class="btn btn-mini btn-xs btn-<?php echo $theme;?><?php echo ($found !== false && $found == 1)  ? ' btn-success' : '';?>" 
						href="#" title="<?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_LIKE');?>" data-toggle="tooltip"
						onclick="document.adminForm.cid.value=<?php echo $this->reply->id?>;CjForumApi.submitAjaxForm(this, '#adminForm', 'rating.rlike', 'onBeforeLike', 'onAfterLike');return false;">
						<i class="fa fa-thumbs-o-up"></i> <span class="user-rating"><?php echo JText::plural('COM_CJFORUM_NUM_LIKES', $this->reply->likes);?></span>
					</a>
				</div>
				<?php 
			}
			else
			{
				?>
				<div class="btn-group">
					<button class="btn btn-mini btn-xs btn-default" type="button" title="<?php echo JText::_('COM_CJFORUM_LOGIN_TO_VOTE');?>" data-toggle="tooltip">
						<?php echo JText::plural('COM_CJFORUM_NUM_DISLIKES', $this->reply->dislikes);?>
					</button>
					<button class="btn btn-mini btn-xs btn-default" type="button" title="<?php echo JText::_('COM_CJFORUM_LOGIN_TO_VOTE');?>" data-toggle="tooltip">
						<?php echo JText::plural('COM_CJFORUM_NUM_LIKES', $this->reply->likes);?>
					</button>
				</div>
				<?php
			}
		}
		?>
		
		<?php if(!$user->guest):?>
		<div class="btn-group">
			<?php if ($isaidthankyou):?>
			<a class="btn btn-mini btn-xs btn-success" href="#" title="<?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_REMOVE_THANKYOU');?>" data-toggle="tooltip"
				onclick="<?php echo ($found === false || $found == 2) ? 'document.adminForm.cid.value='.$this->reply->id.';Joomla.submitbutton(\'reply.nothankyou\'); ' : '';?>return false;">
				<?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_THANKYOU');?>
			</a>
			<?php else:?>
			<a class="btn btn-mini btn-xs btn-default" href="#" title="<?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_THANKYOU');?>" data-toggle="tooltip"
				onclick="<?php echo ($found === false || $found == 2) ? 'document.adminForm.cid.value='.$this->reply->id.';Joomla.submitbutton(\'reply.thankyou\'); ' : '';?>return false;">
				<?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_THANKYOU');?>
			</a>
			<?php endif;?>
		</div>
		<?php endif;?>
	</div>
</div>