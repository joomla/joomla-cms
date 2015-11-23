<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  mod_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2015 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if ( $suggestion->list ) 
{
	?>
	<style>
	<!--
	#cj-wrapper .forum-info, #cj-wrapper .forum-info > li:first-child {margin-left: 0; padding-left: 0;}
	#cj-wrapper .forum-info li {margin-right: 0; padding: 0;}
	-->
	</style>
	<div class="topics-list">
	<?php
	foreach ($suggestion->list as $i=>$item)
	{
		$author 		= CjLibUtils::escape($item->author);
		$profileUrl 	= $api->getUserProfileUrl($profileComponent, $item->created_by);
		$topicUrl		= CjForumHelperRoute::getTopicRoute($item->slug, $item->catslug, $item->language);
		
		if($i > 0)
		{
			echo '<hr style="margin: 10px 0;"/>';
		}
		?>
		<div class="media">
			<?php if($show_avatar && $avatarComponent != 'none'):?>
			<div class="media-left hidden-phone hidden-xs">
				<?php if($profileComponent != 'none'):?>
				<a href="<?php echo $profileUrl;?>" title="<?php echo $author?>" class="thumbnail" data-toggle="tooltip" style="margin-bottom: 0;">
					<img src="<?php echo $api->getUserAvatarImage($avatarComponent, $item->created_by, $item->author_email, $avatar_size, true);?>" 
						alt="<?php echo $author;?>" style="max-width: <?php echo $avatar_size;?>px;" class="media-object">
				</a>
				<?php else:?>
				<div class="thumbnail" style="margin-bottom: 0;">
					<img src="<?php echo $api->getUserAvatarImage($avatarComponent, $item->created_by, $item->author_email, $avatar_size, true);?>" 
						alt="<?php echo $author;?>" style="max-width: <?php echo $avatar_size;?>px;" class="media-object">
				</div>
				<?php endif;?>
			</div>
			<?php endif;?>
			
			<div class="media-body">
				<?php
				if (in_array($item->access, $user->getAuthorisedViewLevels()))
				{
					?>
					<a href="<?php echo JRoute::_($topicUrl); ?>"><?php echo CjLibUtils::escape($item->title); ?></a>
					<?php
				}
				else 
				{
					echo CjLibUtils::escape($item->title) . ' : ';
					
					$itemId = JFactory::getApplication()->getMenu()->getActive()->id;
					$fullURL = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId.'&return='.base64_encode(JRoute::_($topicUrl)));
					?>
					<a href="<?php echo $fullURL; ?>" class="register">
						<?php echo JText::_('COM_CJFORUM_REGISTER_TO_READ_MORE'); ?>
					</a>
					<?php
				}
				
				if($show_author || $show_description || $show_replies)
				{
					?>
					<ul class="inline list-inline forum-info">
						<?php 
						if($show_author)
						{
							?>
							<li class="muted">
								<?php
								if($profileComponent != 'none')
								{
									$profileLink = JHtml::link($profileUrl, $item->author);
									echo JText::sprintf('COM_CJFORUM_POSTED_BY', JHtml::link($profileUrl, $author));
								}
								else
								{
									echo JText::sprintf('COM_CJFORUM_POSTED_BY', $author);
								}
								?>
							</li>
							<?php 
						}
						
						if($show_category)
						{
							?>
							<li class="muted">
								<?php echo JText::sprintf('COM_CJFORUM_CATEGORY_IN', JHtml::link(CjForumHelperRoute::getCategoryRoute($item->catid, $item->language), $item->category_title));?>
							</li>
							<?php 
						}
						
						if($show_date)
						{
							?>
							<li class="muted">
								<?php echo CjLibDateUtils::getHumanReadableDate($item->created);?>.
							</li>
							<?php 
						}
						
						if($show_replies)
						{
							?>
							<li class="visible-phone visible-xs muted text-muted">
								<?php echo JText::plural('COM_CJFORUM_REPLIES_TEXT', $item->replies);?>
							</li>
							<?php 
						}
						?>
					</ul>
					<?php 
				}
				
				if($show_description == 1)
				{
					echo '<div class="muted text-muted">'.JHtml::_('string.truncate', strip_tags($item->introtext), $introtext_length).'</div>';
				}
				?>
			</div>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
?>