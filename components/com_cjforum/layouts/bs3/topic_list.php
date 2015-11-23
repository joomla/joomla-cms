<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$user = JFactory::getUser();

$params 			= $displayData['params'];
$state				= isset($displayData['state']) ? $displayData['state'] : null;
$pagination 		= $displayData['pagination'];
$items 				= $displayData['topics'];
$theme 				= $params->get('theme', 'default');
$avatar  			= $params->get('avatar_component', 'cjforum');
$profileComponent 	= $params->get('profile_component', 'cjforum');
$avatarSize 		= $params->get('list_avatar_size', 48);
$repliesLimit		= $params->get('replies_limit', 10);
$heading 			= isset($displayData['heading']) ? $displayData['heading'] : JText::_('COM_CJFORUM_TOPICS');
$subHeading 		= '';

$category = isset($displayData['category']) ? $displayData['category'] : null;
$subHeading = $category ? ' <small>['.$this->escape($category->title).']</small>' : $subHeading;

if(is_object($state))
{
	$authorId = $state->get('filter.author_id', 0);
	if($authorId)
	{
		$author = JFactory::getUser($authorId);
		$subHeading = $subHeading.' <small>['.$author->name.']</small>';
	}
	
	$featured = $state->get('filter.featured'. '');
	if($featured == 'only')
	{
		$subHeading = $subHeading.' <small>['.JText::_('COM_CJFORUM_LABEL_FEATURED_TOPICS').']</small>';
	}
	
	$unanswered = $state->get('filter.unanswered', 0);
	if($unanswered == 1)
	{
		$subHeading = $subHeading.' <small>['.JText::_('COM_CJFORUM_LABEL_UNANSWERED_TOPICS').']</small>';
	}
	
	$ordering = $state->get('list.ordering', '');
	if($ordering == 'hits')
	{
		$subHeading = $subHeading.' <small>['.JText::_('COM_CJFORUM_LABEL_POPULAR_TOPICS').']</small>';
	}
	
	$recent = $state->get('list.recent', false);
	if($recent == true)
	{
		$subHeading = $subHeading.' <small>['.JText::_('COM_CJFORUM_RECENT_TOPICS').']</small>';
	}
}

if(!empty($items))
{
$api = new CjLibApi();
?>

<div class="panel panel-<?php echo $theme;?> topics-list-wrap">

	<?php if(!empty($heading)):?>
	<div class="panel-heading">
		<div class="panel-title"><?php echo $heading.$subHeading;?></div>
	</div>
	<?php endif;?>
	
	<ul class="list-group no-margin-left topics-list">
	<?php 
	foreach ($items as $i=>$item)
	{
		$author = $this->escape($item->author);
		$profileUrl = $api->getUserProfileUrl($profileComponent, $item->created_by);
		$topicUrl = CjForumHelperRoute::getTopicRoute($item->slug, $item->catslug, $item->language);
		$userAvatar = $api->getUserAvatarImage($avatar, $item->created_by, $item->author_email, $avatarSize, true);
		$lastReplyUrl = JRoute::_($topicUrl.($item->page_start > 0 ? '&start='.$item->page_start : '').($item->last_reply ? '#p'.$item->last_reply : ''));
		$lastReplyDate = $item->replied_by > 0 ? CjLibDateUtils::getHumanReadableDate($item->replied) : null;
		?>
		<li class="list-group-item<?php echo $item->featured ? ' list-group-item-warning' : '';?> pad-bottom-5">
			<div class="media">
				
				<?php if($avatar != 'none'):?>
				<div class="media-left hidden-xs hidden-phone">
					<?php if($profileComponent != 'none'):?>
					<a href="<?php echo $profileUrl;?>" title="<?php echo $author?>" class="thumbnail no-margin-bottom" data-toggle="tooltip">
						<img src="<?php echo $userAvatar;?>" alt="<?php echo $author;?>" class="media-object" style="min-width: <?php echo $avatarSize;?>px">
					</a>
					<?php else:?>
					<div class="thumbnail">
						<img src="<?php echo $userAvatar;?>" alt="<?php echo $author;?>" class="media-object" style="min-width: <?php echo $avatarSize;?>px">
					</div>
					<?php endif;?>
				</div>
				<?php endif;?>
				
				<div class="media-body">
					<h4 class="media-heading no-margin-top">
						<?php
						if (in_array($item->access, $user->getAuthorisedViewLevels()))
						{
							?>
							<a href="<?php echo JRoute::_($topicUrl); ?>" title="<?php echo JHtml::_('string.truncate', strip_tags($item->introtext), 250);?>">
								<?php echo $this->escape($item->title); ?>
							</a>
							<?php
							 
							if($item->new_posts)
							{
								?>
								<span title="<?php echo JText::_('COM_CJFORUM_VIEW_LAST_REPLY');?>" data-toggle="tooltip" class="new-posts-text">
									<a href="<?php echo $lastReplyUrl;?>">(<?php echo JText::sprintf('COM_CJFORUM_NEW_POSTS_TEXT', $item->new_posts)?>)</a>
								</span>
								<?php
							}
						}
						else 
						{
							echo $this->escape($item->title) . ' : ';
							
							$itemId = JFactory::getApplication()->getMenu()->getActive()->id;
							$fullURL = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId.'&return='.base64_encode(JRoute::_($topicUrl)));
							?>
							<a href="<?php echo $fullURL; ?>" class="register">
								<?php echo JText::_('COM_CJFORUM_REGISTER_TO_READ_MORE'); ?>
							</a>
							<?php
						}
						?>
					</h4>
					
					<?php 
					if($repliesLimit > 0 && $item->replies > $repliesLimit)
					{
						$maxLinks = ceil($item->replies / $repliesLimit);
						$displayLinks = $maxLinks > 5 ? 5 : $maxLinks;
						?>
						<nav>
							<ul class="pagination pagination-xs">
								<?php for($i = 0; $i < $displayLinks; $i++):?>
								<li><a href="<?php echo JRoute::_($topicUrl.($i > 0 ? '&start='.($i * $repliesLimit) : ''));?>"><?php echo $i + 1;?></a></li>
								<?php endfor;?>
								
								<?php if($maxLinks > 5):?>
								<li><a href="<?php echo JRoute::_($topicUrl.'&start='.(($maxLinks - 1) * $repliesLimit));?>">..<?php echo $maxLinks;?></a></li>
								<?php endif;?>
							</ul>
						</nav>
						<?php 
					}
					?>
					
					<ul class="inline list-inline forum-info">
						<?php if($item->state == 0 || $item->state == -2 || $item->featured == 1):?>
						<li>
							<?php if($item->featured == 1):?>
							<span class="label label-success"><?php echo JText::_('JFEATURED');?></span>
							<?php endif;?>
							
							<?php if($item->locked == 1):?>
							<span class="label label-warning"><?php echo JText::_('COM_CJFORUM_LOCKED');?></span>
							<?php endif;?>
							
							<?php if($item->state == 0):?>
							<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED');?></span>
							<?php endif;?>
							
							<?php if($item->state == -2):?>
							<span class="label label-danger"><?php echo JText::_('JTRASHED');?></span>
							<?php endif;?>
						</li>
						<?php endif;?>
						<li class="muted">
							<?php 
							if($profileComponent != 'none')
							{
								$profileLink = JHtml::link($profileUrl, $item->author);
								echo JText::sprintf('COM_CJFORUM_POSTED_BY', $profileLink);
							}
							else
							{
								echo JText::sprintf('COM_CJFORUM_POSTED_BY', $author);
							}
							?>
						</li>
						<?php if($params->get('list_show_parent', 1) == 1):?>
						<li class="muted">
							<?php echo JText::sprintf('COM_CJFORUM_CATEGORY_IN', JHtml::link(CjForumHelperRoute::getCategoryRoute($item->catid, $item->language), $item->category_title));?>
						</li>
						<?php endif;?>
						
						<?php if(isset($item->displayDate)):?>
						<li class="muted">
							<?php echo CjLibDateUtils::getHumanReadableDate($item->displayDate);?>.
						</li>
						<?php endif;?>
						
						<?php if($lastReplyDate && $params->get('list_show_reply_date', 1) == 1):?>
						<li class="muted"><?php echo JText::sprintf('COM_CJFORUM_LAST_REPLY_DATE_TEXT', $lastReplyUrl, $lastReplyDate);?></li>
						<?php endif;?>
						
						<li class="visible-phone visible-xs muted text-muted">
							<?php echo JText::plural('COM_CJFORUM_REPLIES_TEXT', $item->replies);?>
						</li>
					</ul>
				</div>

				<?php 
				if($item->replied_by > 0)
				{
					$replyAuthor = $this->escape($item->reply_author);
					$tooltip = JText::sprintf('COM_CJFORUM_LAST_REPLY_TEXT', $replyAuthor, $lastReplyDate);
					?>
					<div class="media-right hidden-xs hidden-phone">
						<div class="no-margin-bottom" title="<?php echo $tooltip;?>" data-toggle="tooltip">
							<a href="<?php echo $lastReplyUrl;?>">
								<span class="fa-stack fa-2x">
									<i class="fa fa-comment fa-stack-2x"></i>
									<strong class="fa-stack-1x reply-icon-text"><?php echo strtoupper(substr(trim($item->reply_author), 0, 1));?></strong>
								</span>
							</a>
						</div>
					</div>
				<?php 
				}
				?>
				
				<div class="media-right hidden-xs hidden-phone">
					<div class="panel panel-<?php echo $theme;?> item-count-box">
						<div class="panel-body center item-count-num"><?php echo $item->replies;?></div>
						<div class="panel-footer text-nowrap text-muted item-count-caption"><?php echo JText::plural('COM_CJFORUM_REPLIES', $item->replies);?></div>
					</div>
				</div>
				
				<?php if($params->get('list_show_hits', 1) == 1):?>
				<div class="media-right hidden-xs hidden-phone">
					<div class="panel panel-<?php echo $theme;?> item-count-box">
						<div class="panel-body center item-count-num"><?php echo CjLibUtils::formatNumber($item->hits);?></div>
						<div class="panel-footer text-nowrap text-muted item-count-caption"><?php echo JText::plural('COM_CJFORUM_HITS', $item->hits);?></div>
					</div>
				</div>
				<?php endif;?>
				
			</div>
		</li>
		<?php
		if(count(JModuleHelper::getModules('topics-view-after-topic-'.($i + 1))))
		{
			echo CJFunctions::load_module_position('topics-view-after-topic-'.($i+1));
		}
	}
	?>
	</ul>
</div>

<?php if (!empty($items)) : ?>
	<?php if (($params->def('show_pagination', 2) == 1  || ($params->get('show_pagination') == 2)) && ($pagination->pagesTotal > 1)) : ?>
		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="no-margin-bottom clearfix">
			<div class="pagination margin-bottom-10 margin-top-10">
				<?php if ($params->def('show_pagination_results', 1)) : ?>
					<p class="counter pull-right">
						<?php echo $pagination->getPagesCounter(); ?>
					</p>
				<?php endif; ?>
		
				<?php echo $pagination->getPagesLinks(); ?>
			</div>
		</form>
	<?php endif; ?>
<?php  endif; ?>

<?php
}
else if($params->get('show_no_topics'))
{
	?>
	<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo JText::_('COM_CJFORUM_NO_TOPICS')?></div>
	<?php 
}
?>