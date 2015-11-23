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
$params  			= $this->item->params;
$avatarApp			= $params->get('avatar_component', 'cjforum');
$avatarSize			= $params->get('topic_avatar_size', 96);
$profileApp			= $params->get('profile_component', 'cjforum');
$pointsApp			= $params->get('points_component', 'none');
$align	 			= $params->get('profile_alignment', 'left');
$layout 			= $params->get('layout', 'default');
$start				= $app->input->getInt('start', 0);
$topic_uri			= CjForumHelperRoute::getTopicRoute($this->item->slug, $this->item->catslug, $this->item->language, $start);
$images 			= json_decode($this->item->images);
$urls 				= json_decode($this->item->urls);
$useDefList 		= ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date') || $params->get('show_hits') ||
		 				$params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author'));
$theme 				= $params->get('theme', 'default');

$author_url			= $api->getUserProfileUrl($profileApp, $this->item->created_by);
$author_name		= $microdata->content($this->escape($this->item->author))->property('name')->fallback('Person', 'name')->display();
$topic_author		= $profileApp != 'none' ? JHtml::link($author_url, $author_name, array('itemprop'=>'url')) : $author_name;
$profileApi			= CjForumApi::getProfileApi();
?>
<div class="topic-body" <?php echo $microdata->displayScope();?>>
	<div class="panel panel-<?php echo $theme;?>">
		<div class="panel-heading topic-head">
			<div class="media">
				<div class="media-left hidden-phone hidden-xs">
					<a href="<?php echo $author_url;?>"	class="thumbnail no-margin-bottom " title="<?php echo $this->escape($this->item->author);?>" data-toggle="tooltip">
						<img src="<?php echo $api->getUserAvatarImage($avatarApp, $this->item->created_by, $this->item->author_email, 48, true);?>" 
							class="media-object" alt="<?php echo $this->escape($this->item->author);?>" style="max-width: 48px;">
					</a>
				</div>
				
				<div class="media-body">
					<h1 class="panel-title">
						<?php echo JHtml::link(JRoute::_($topic_uri), $microdata->content($this->item->title)->property('headline')->fallback('Text', 'headline')->display());?>
					</h1>
					<div class="margin-bottom-5 margin-top-5">
						<span class="author-name" <?php echo $microdata->htmlProperty('author');?>>
							<?php echo JText::sprintf('COM_CJFORUM_WRITTEN_BY', $topic_author);?>
						</span>
						<span class="category-title">
							<?php echo JText::sprintf('COM_CJFORUM_TOPIC_CATEGORY_TEXT', JHtml::link(CjForumHelperRoute::getCategoryRoute($this->item->catid, $this->item->language), $this->item->category_title));?>
						</span>
						<span class="topic-date">
							<?php echo $microdata->content(CjLibDateUtils::getHumanReadableDate($this->item->created))->property('dateCreated')->fallback('Date', 'dateCreated')->display();?>
						</span>
					</div>
		
					<?php 
					if($params->get('social_sharing', 1) == 1)
					{
						$document = JFactory::getDocument();
						$document->addScript('//s7.addthis.com/js/300/addthis_widget.js#async=1');
						$document->addScriptDeclaration('jQuery(document).ready(function($){addthis.init();});');
						
						if($this->state->get('list.offset') == 0)
						{
						?>
						<div id="social">
							<!-- AddThis Button BEGIN -->
							<div class="addthis_toolbox addthis_default_style addthis_16x16_style">
								<a class="addthis_button_twitter"></a>
								<a class="addthis_button_facebook"></a>
								<a class="addthis_button_google_plusone_share"></a>
								<a class="addthis_button_linkedin"></a>
								<a class="addthis_button_pinterest_share"></a>
								<a class="addthis_button_email"></a>
								<a class="addthis_button_compact"></a><a class="addthis_counter addthis_bubble_style"></a>
							</div>
						</div>
						<?php 
						}
					}
					?>
				</div>
			</div>
			
			<?php echo $this->item->event->afterDisplayTitle; ?>
		</div>
		
		<?php 
		if($this->state->get('list.offset') == 0)
		{
			?>
			<div class="panel-body">
				<?php
				echo $this->item->event->beforeDisplayContent;
				echo $microdata->content($this->item->text)->property('text')->display();
				echo $this->item->event->afterDisplayContent;
			
				if ($params->get('show_tags', 1) && !empty($this->item->tags))
				{
					$this->item->tagLayout = new JLayoutFile('joomla.content.tags');
					echo $this->item->tagLayout->render($this->item->tags->itemTags);
				}
				
				if(!empty($this->item->attachments))
				{
					echo JLayoutHelper::render($layout.'.attachments', array('item'=>$this->item, 'params'=>$this->item->params));
				}
				?>
			</div>
			<?php 
		}
		?>
		
		<div class="panel-footer"<?php echo ($this->state->get('list.offset') == 0) ? '' : ' style="border-top: 0;"'?>>
			<ul class="list-inline inline no-space-left" role="menu" aria-labelledby="dLabel" style="margin: 0;">
				<?php 
				if($params->get('enable_ratings', true))
				{
					if(!$user->guest && $user->authorise('core.vote', 'com_cjforum.topic.'.$this->item->id))
					{
						$found = false;
						if(!empty($this->likes))
						{
							foreach ($this->likes as $like)
							{
								if($like->item_type == ITEM_TYPE_TOPIC && $like->item_id == $this->item->id)
								{
									$found = $like->action_value;
									break;
								}
							}
						}
						?>
						<li style="margin-left: 0; padding-left: 0">
							<div class="btn-group user-ratings">
								<a class="btn btn-mini btn-xs btn-<?php echo $theme;?><?php echo ($found !== false && $found == 2)  ? ' btn-danger' : '';?>" href="#" 
									title="<?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_DISLIKE');?>" data-toggle="tooltip" 
									onclick="document.adminForm.cid.value=<?php echo $this->item->id?>;CjForumApi.submitAjaxForm(this, '#adminForm', 'rating.tdislike', 'onBeforeLike', 'onAfterLike');return false;">
									<i class="fa fa-thumbs-o-down"></i> <span class="user-rating"><?php echo JText::plural('COM_CJFORUM_NUM_DISLIKES', $this->item->dislikes);?></span>
								</a>
								
								<a class="btn btn-mini btn-xs btn-<?php echo $theme;?><?php echo ($found !== false && $found == 1)  ? ' btn-success' : '';?>" 
									href="#" title="<?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_LIKE');?>" data-toggle="tooltip"
									onclick="document.adminForm.cid.value=<?php echo $this->item->id?>;CjForumApi.submitAjaxForm(this, '#adminForm', 'rating.tlike', 'onBeforeLike', 'onAfterLike');return false;">
									<i class="fa fa-thumbs-o-up"></i> <span class="user-rating"><?php echo JText::plural('COM_CJFORUM_NUM_LIKES', $this->item->likes);?></span>
								</a>
							</div>
						</li>
						<?php
					}
					else 
					{
						?>
						<li>
							<div class="btn-group">
								<button class="btn btn-mini btn-xs btn-default" type="button" title="<?php echo JText::_('COM_CJFORUM_LOGIN_TO_VOTE');?>" data-toggle="tooltip">
									<?php echo JText::plural('COM_CJFORUM_NUM_DISLIKES', $this->item->dislikes);?>
								</button>
								<button class="btn btn-mini btn-xs btn-default" type="button" title="<?php echo JText::_('COM_CJFORUM_LOGIN_TO_VOTE');?>" data-toggle="tooltip">
									<?php echo JText::plural('COM_CJFORUM_NUM_LIKES', $this->item->likes);?>
								</button>
							</div>
						</li>
						<?php
					}
				}
				
				if(!$user->guest)
				{
					?>
					<li>
						<?php if($this->item->favorite > 0):?>
						<a class="btn btn-mini btn-xs btn-<?php echo $theme;?> btn-success" data-toggle="tooltip" title="<?php echo JText::_('COM_CJFORUM_TOPIC_ADDED_TO_FAVORITE');?>"
							href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topic.unfavorite');return false;">
							<i class="fa fa-heart-o"></i> <?php echo JText::_('COM_CJFORUM_REMOVE_FAVORITE');?>
						</a>
						<?php else:?>
						<a class="btn btn-mini btn-xs btn-<?php echo $theme;?>" 
							href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topic.favorite');return false;">
							<i class="fa fa-heart-o"></i> <?php echo JText::_('COM_CJFORUM_ADD_FAVORITE');?>
						</a>
						<?php endif;?>
					</li>
					<?php
				}
				
				if($user->authorise('core.moderate', 'com_cjforum.topic.'.$this->item->id))
				{ // allow only moderators to do the featured bit changes
					if($this->item->featured == 1)
					{
					?>
					<li>
						<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topics.unfeatured');return false;">
							<i class="fa fa-star"></i> <?php echo JText::_('COM_CJFORUM_UNFEATURE');?>
						</a>
					</li>
					<?php
					}
					else 
					{
					?>
					<li>
						<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topics.featured');return false;">
							<i class="fa fa-star"></i> <?php echo JText::_('COM_CJFORUM_FEATURE');?>
						</a>
					</li>
					<?php
					}
				}
				
				if($this->item->params->get('access-edit'))
				{ // allow editing topic if user has edit permission or the user is owner of the topic with edit own permissions
					?>
					<li>
						<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topic.edit');return false;">
							<i class="fa fa-pencil"></i> <?php echo JText::_('JGLOBAL_EDIT');?>
						</a>
					</li>
					<?php
				}
				
				if($this->item->params->get('access-edit-state'))
				{ // allow changing state of the topic if user has edit state permission or the user is owner of the topic with edit own state permissions
					if($this->item->locked == 0)
					{
						?>
						<li>
							<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topics.lock'); return false;">
								<i class="fa fa-lock"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_LOCK');?>
							</a>
						</li>
						<?php
					}
					else
					{
						?>
						<li>
							<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topics.unlock'); return false;">
								<i class="fa fa-unlock"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_UNLOCK');?>
							</a>
						</li>
						<?php
					}
					
					if($this->item->state == 1)
					{
					?>
					<li>
						<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topics.unpublish'); return false;">
							<i class="fa fa-ban"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_UNPUBLISH');?>
						</a>
					</li>
					<?php
					} else {
					?>
					<li>
						<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topics.publish');return false;">
							<i class="fa fa-eye"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_PUBLISH');?>
						</a>
					</li>
					<?php
					}
				}
				
				if($user->authorise('core.delete', 'com_cjforum.topic.'.$this->item->id) || $user->authorise('core.delete.own', 'com_cjforum.topic.'.$this->item->id))
				{ // allow if user has delete permissions or user is owner of the topic with delete own permissions
					if($this->item->state != -2)
					{
					?>
					<li>
						<a href="#" onclick="document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topics.trash');return false;">
							<i class="fa fa-trash-o"></i> <?php echo JText::_('JTRASH');?>
						</a>
					</li>
					<?php
					}
					else 
					{
					?>
					<li>
						<a href="#" onclick="document.adminForm.return.value='';document.adminForm.cid.value=<?php echo $this->item->id;?>;Joomla.submitbutton('topics.delete'); return false;">
							<i class="fa fa-warning"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_PERMANANTLY_DELETE');?>
						</a>
					</li>
					<?php
					}
				}
				?>
			</ul>
		</div>
	</div>
</div>