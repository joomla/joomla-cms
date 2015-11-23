<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2015 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('script', 'system/core.js', false, true);

$user = JFactory::getUser();

$params 			= $displayData['params'];
$pagination 		= $displayData['pagination'];
$items 				= $displayData['activities'];
$comments_limit		= $displayData['limit'];
$comments_start		= $displayData['start'];
$theme 				= $params->get('theme', 'default');
$avatarComponent  	= $params->get('avatar_component', 'cjforum');
$profileComponent	= $params->get('profile_component', 'cjforum');

$api = new CjLibApi();
$loggedInUserAvatar = $api->getUserAvatarImage($avatarComponent, $user->id, $user->email, 32);
?>
<div class="panel panel-<?php echo $theme;?> margin-bottom-10 activity">
	<div class="panel-heading">
		<div class="panel-title"><?php echo JText::_('COM_CJFORUM_ACTIVITY_HOME')?></div>
	</div>
	<ul class="list-group no-space-left">
		<?php 
		if(!empty($items))
		{
			foreach ($items as $item)
			{
				$author = $this->escape($item->author);
				$profileUrl = $api->getUserProfileUrl($profileComponent, $item->created_by);
				$avatarImage = $api->getUserAvatarImage($avatarComponent, $item->created_by, $item->author_email, 36);
				?>
				<li class="list-group-item no-margin-left">
					<div class="media">
						<div class="media-left hidden-phone">
							<?php 
							if($avatarComponent != 'none')
							{
								if($profileComponent != 'none')
								{
									?>
									<a href="<?php echo $profileUrl;?>" class="thumbnail no-margin-bottom">
										<img alt="<?php echo $author?>" src="<?php echo $avatarImage;?>" class="media-object" style="max-width: 36px;">
									</a>
									<?php 
								}
								else 
								{
									?>
									<div class="thumbnail">
										<img alt="<?php echo $author?>" src="<?php echo $avatarImage;?>">
									</div>
									<?php 
								}
							}
							?>
						</div>
						<div class="media-body">
						
							<p class="media-heading no-margin-top"><?php echo $item->title; ?></p>
							<p class="badge badge-success visible-phone"><?php echo CjForumApi::getActivityDate($item->created);?></p>
							<div class="description text-muted"><small><?php echo strip_tags($item->description);?></small></div>
							
							<?php if($user->authorise('core.edit.state', 'com_cjforum') || $user->authorise('core.delete', 'com_cjforum')):?>
							<ul class="list-inline inline no-space-left" role="menu" aria-labelledby="dLabel" style="margin: 0;">
								<?php 
								if($user->authorise('core.edit.state', 'com_cjforum'))
								{ // allow changing state of the topic if user has edit state permission or the user is owner of the topic with edit own state permissions
									if($item->published == 1)
									{
									?>
									<li>
										<a href="#" onclick="document.adminForm.cid.value=<?php echo $item->id;?>;Joomla.submitbutton('activities.unpublish'); return false;">
											<small class="text-muted"><i class="fa fa-ban"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_UNPUBLISH');?></small>
										</a>
									</li>
									<?php
									} else {
									?>
									<li>
										<a href="#" onclick="document.adminForm.cid.value=<?php echo $item->id;?>;Joomla.submitbutton('activities.publish');return false;">
											<small class="text-muted"><i class="fa fa-eye"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_PUBLISH');?></small>
										</a>
									</li>
									<?php
									}
								}
								
								if($user->authorise('core.delete', 'com_cjforum'))
								{ // allow if user has delete permissions or user is owner of the topic with delete own permissions
									if($item->published != -2)
									{
									?>
									<li>
										<a href="#" onclick="document.adminForm.cid.value=<?php echo $item->id;?>;Joomla.submitbutton('activities.trash');return false;">
											<small class="text-muted"><i class="fa fa-trash-o"></i> <?php echo JText::_('JTRASH');?></small>
										</a>
									</li>
									<?php
									}
									else 
									{
									?>
									<li>
										<a href="#" onclick="document.adminForm.return.value='';document.adminForm.cid.value=<?php echo $item->id;?>;Joomla.submitbutton('activities.delete'); return false;">
											<small class="text-muted"><i class="fa fa-warning"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_PERMANANTLY_DELETE');?></small>
										</a>
									</li>
									<?php
									}
								}
								?>
							</ul>
							<?php endif;?>
						</div>
						<div class="media-right hidden-phone">
							<p class="badge badge-success" title="<?php echo CjForumApi::getActivityDate($item->created);?>" data-toggle="tooltip">
								<?php echo CjLibDateUtils::getHumanReadableDate($item->created);?>
							</p>
						</div>
					</div>
				</li>
				<?php
			}
		}
		else
		{
			?>
			<li class="list-group-item">
				<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo JText::_('COM_CJFORUM_NO_RESULTS_FOUND')?></div>
			</li>
			<?php 
		}
		?>
	</ul>
</div>
	
<?php 
if(!empty($items))
{
	if (($params->def('show_pagination', 2) == 1  || ($params->get('show_pagination') == 2)) && ($pagination->pagesTotal > 1))
	{ 
		?>
		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="no-margin-bottom">
			<div class="pagination margin-bottom-10 margin-top-10">
				<?php if ($params->def('show_pagination_results', 1)) : ?>
					<p class="counter pull-right">
						<?php echo $pagination->getPagesCounter(); ?>
					</p>
				<?php endif; ?>
				<?php echo $pagination->getPagesLinks(); ?>
			</div>
		</form>
		<?php 
	}
}
?>
