<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$theme 				= $this->params->get('theme', 'default');
$avatar  			= $this->params->get('avatar_component', 'cjforum');
$profileComponent 	= $this->params->get('profile_component', 'cjforum');
$layout 			= $this->params->get('layout', 'default');
$displayName		= $this->params->get('display_name', 'name');

$api = new CjLibApi();
?>
<div id="cj-wrapper" class="leaderboard<?php echo $this->pageclass_sfx;?>">
	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));?>
	
	<div class="panel panel-default">
		<div class="panel-heading"><?php echo JText::_('COM_CJFORUM_LEADERBOARD');?></div>
		<ul class="list-group no-space-left">
			<?php 
			if(!empty($this->items))
			{
				foreach ($this->items as $rank=>$item)
				{
					$author = $this->escape($item->$displayName);
					$profileUrl = $api->getUserProfileUrl($profileComponent, $item->id);
					$userAvatar = $api->getUserAvatarImage($avatar, $item->id, $item->email, 64, true);
					?>
					<li class="list-group-item no-margin-left">
						<div class="media">
							<div class="media-left">
								<div class="panel panel-success leader-rank-box" style="min-width: 75px; min-height: 72px;">
									<h2 class="leader-rank center text-center"><?php echo $rank + 1;?></h2>
									<div class="muted text-muted center text-center"><?php echo JText::_('COM_CJFORUM_RANK_LABEL');?></div>
								</div>
							</div>
							
							<?php if($avatar != 'none'):?>
							<div class="media-left hidden-phone hidden-xs">
								<?php if($profileComponent != 'none'):?>
								<a href="<?php echo $profileUrl;?>" title="<?php echo $author?>" class="thumbnail no-margin-bottom" data-toggle="tooltip">
									<img src="<?php echo $userAvatar;?>" alt="<?php echo $author;?>" style="max-width: 64px;">
								</a>
								<?php else:?>
								<div class="thumbnail no-margin-bottom">
									<img src="<?php echo $userAvatar;?>" alt="<?php echo $author;?>" style="max-width: 64px;">
								</div>
								<?php endif;?>
							</div>
							<?php endif;?>
							<div class="media-body">
								<h4 class="margin-top-5 margin-bottom-5"><?php echo $author;?></h4>
								<div class="muted text-muted margin-bottom-10"><strong><?php echo JText::sprintf('COM_CJFORUM_NUM_KARMA', $item->karma)?></strong></div>
								<div class="muted text-muted">
									<ul class="unstyled inline list-unstyled list-inline">
										<li class="no-pad-left"><i class="fa fa-comments"></i> <?php echo JText::sprintf('COM_CJFORUM_NUM_TOPICS', $item->topics);?></li>
										<li class="no-pad-left"><i class="fa fa-comments-o"></i> <?php echo JText::sprintf('COM_CJFORUM_NUM_REPLIES', $item->replies);?></li>
										<li class="no-pad-left"><i class="fa fa-thumbs-o-up"></i> <?php echo JText::sprintf('COM_CJFORUM_NUM_THANKYOU', $item->thankyou);?></li>
									</ul>
								</div>
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
				<div class="alert alert-info"><?php echo JText::_('MSG_NO_RESULTS'); ?></div>
			</li>
			<?php 
			}
			?>
		</ul>
	</div>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
</div>
