<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2015 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$user 			= JFactory::getUser();
$params 		= $displayData['params'];
$state 			= $displayData['state'];
$return			= base64_encode(JRoute::_('index.php'));
$messages 		= CjForumApi::checkMessages($user->id);
$featured 		= is_object($state) ? $state->get('filter.featured') : null;
$category		= $params->get('catid', 0);
?>

<nav class="navbar navbar-default" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#cf-navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo JRoute::_('index.php?option=com_cjforum');?>">
				<?php echo JText::_('COM_CJFORUM_LABEL_HOME');?>
			</a>
		</div>
		<div class="navbar-collapse" id="cf-navbar-collapse">
			<ul class="nav navbar-nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
						<?php echo JText::_('COM_CJFORUM_LABEL_DISCOVER');?> <b class="caret"></b>
					</a>
					<ul class="dropdown-menu" role="menu">
						<li>
							<a href="<?php echo CjForumHelperRoute::getCategoryRoute();?>">
								<i class="fa fa-home"></i> <?php echo JText::_('COM_CJFORUM_FORUM_INDEX')?>
							</a>
						</li>
						<li>
							<a href="<?php echo CjForumHelperRoute::getTopicsRoute().'&recent=true';?>">
								<i class="fa fa-tasks"></i> <?php echo JText::_('COM_CJFORUM_RECENT_TOPICS')?>
							</a>
						</li>
						<?php if($params->get('stream_component', 'cjforum') == 'cjforum'):?>
						<li>
							<a href="<?php echo CjForumHelperRoute::getActivityRoute();?>">
								<i class="fa fa-tasks"></i> <?php echo JText::_('COM_CJFORUM_ACTIVITY_HOME')?>
							</a>
						</li>
						<?php endif;?>
		
						<li class="divider"></li>
						<li<?php echo (is_object($state) && $state->get('list.ordering') == 'hits') ? ' class="active"' : '';?>>
							<a href="#" onclick="filterTopics('', 'hits', 'desc', 0); return false;">
								<i class="fa fa-fire"></i> <?php echo JText::_('COM_CJFORUM_LABEL_POPULAR_TOPICS')?>
							</a>
						</li>
						
						<li<?php echo (is_object($state) && $state->get('filter.unanswered') == 1) ? ' class="active"' : '';?>>
							<a href="#" onclick="filterTopics('', 'votes', 'desc', 1); return false;">
								<i class="fa fa-leaf"></i> <?php echo JText::_('COM_CJFORUM_LABEL_UNANSWERED_TOPICS')?>
							</a>
						</li>
						
						<li<?php echo !empty($featured) ? ' class="active"' : '';?>>
							<a href="#" onclick="filterTopics('only', 'votes', 'desc', 0); return false;">
								<i class="fa fa-star-o"></i> <?php echo JText::_('COM_CJFORUM_LABEL_FEATURED_TOPICS')?>
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a href="<?php echo CjForumHelperRoute::getUsersRoute();?>">
								<i class="fa fa-users"></i> <?php echo JText::_('COM_CJFORUM_LABEL_MEMBERS')?>
							</a>
						</li>
						<li>
							<a href="<?php echo CjForumHelperRoute::getLeaderBoardRoute();?>">
								<i class="fa fa-trophy"></i> <?php echo JText::_('COM_CJFORUM_LEADERBOARD')?>
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a href="<?php echo CjForumHelperRoute::getSearchRoute();?>">
								<i class="fa fa-search"></i> <?php echo JText::_('COM_CJFORUM_ADVANCED_SEARCH')?>
							</a>
						</li>
					</ul>
				</li>
			</ul>
			<?php if(!$user->guest):?>
			<ul class="nav navbar-nav navbar-right">
				<li>
					<a href="<?php echo CjForumHelperRoute::getFormRoute(0, $category).'&return='.$return;?>">
						<i class="fa fa-edit"></i> <?php echo JText::_('COM_CJFORUM_LABEL_START_NEW_TOPIC');?>
					</a>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
						<?php echo JText::_('COM_CJFORUM_LABEL_ACCOUNT');?> <b class="caret"></b>
					</a>
					<ul class="dropdown-menu dropdown-menu-right" role="menu">
						<li>
							<a href="#" onclick="document.toolbarAuthorForm.submit(); return false;">
								<i class="fa fa-file-o"></i> <?php echo JText::_('COM_CJFORUM_LABEL_MY_TOPICS');?>
							</a>
						</li>
						<li>
							<a href="<?php echo JRoute::_(CjForumHelperRoute::getProfileRoute());?>">
								<i class="fa fa-user"></i> <?php echo JText::_('COM_CJFORUM_LABEL_MY_PROFILE');?>
							</a>
						</li>
						<li>
							<a href="<?php echo JRoute::_(CjForumHelperRoute::getProfileRoute().'&layout=reputation');?>">
								<i class="fa fa-trophy"></i> <?php echo JText::_('COM_CJFORUM_LABEL_MY_POINTS');?>
							</a>
						</li>
						<li>
							<a href="<?php echo JRoute::_(CjForumHelperRoute::getProfileRoute().'&layout=favorites');?>">
								<i class="fa fa-heart-o"></i> <?php echo JText::_('COM_CJFORUM_LABEL_MY_FAVORITES');?>
							</a>
						</li>
					</ul>
				</li>
				<?php if(! $user->guest && 1 == 2):?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages');?>">
						<span class="label label-<?php echo $messages > 0 ? 'success' : 'default';?>"><i class="fa fa-envelope"></i>&nbsp;&nbsp;<strong><?php echo $messages;?></strong></span>
					</a>
				</li>
				<?php endif;?>
			</ul>
			<?php endif;?>
		</div>
	</div>
</nav>

<form id="toolbarAuthorForm" name="toolbarAuthorForm" action="<?php echo JRoute::_('index.php');?>" method="post" style="display: none;">
	<input type="hidden" id="filter_author_id" name="filter_author_id" value="<?php echo $user->id;?>">
	<input type="hidden" id="view" name="view" value="topics">
</form>

<form id="toolbarFilterForm" name="toolbarFilterForm" action="<?php echo JRoute::_('index.php?option=com_cjforum&view=topics');?>" method="post" style="display: none;">
	<input type="hidden" id="filter_featured" name="filter_featured" value="">
	<input type="hidden" id="filter_order" name="filter_order" value="created">
	<input type="hidden" id="filter_order_Dir" name="filter_order_Dir" value="desc">
	<input type="hidden" id="filter_unanswered" name="filter_unanswered" value="0">
	<input type="hidden" id="view" name="view" value="topics">
</form>

<script type="text/javascript">
<!--
function filterTopics(featured, order, direction, unanswered)
{
	document.toolbarFilterForm.filter_featured.value = featured;
	document.toolbarFilterForm.filter_order.value = order;
	document.toolbarFilterForm.filter_order_Dir.value = direction;
	document.toolbarFilterForm.filter_unanswered.value = unanswered;

	document.toolbarFilterForm.submit();
}
//-->
</script>