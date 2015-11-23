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
JHtml::_('behavior.caption');

$profileComponent = $this->params->get('avatar_component', 'cjforum');
$avatarComponent = $this->params->get('user_avatar', 'cjforum');
$layout = $this->params->get('layout', 'default');
$return = base64_encode(JRoute::_('index.php?option=com_cjforum&view=messages', false));
$api = new CjLibApi();
?>
<div id="cj-wrapper" class="pms <?php echo $this->pageclass_sfx;?>">

	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));?>
	
	<ul class="nav nav-tabs margin-bottom-5" role="tablist">
		<li class="active">
			<a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages');?>"><?php echo JText::_('COM_CJFORUM_PMS_INBOX');?></a>
		</li>
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages&layout=sent');?>"><?php echo JText::_('COM_CJFORUM_PMS_SENT');?></a></li>
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages&layout=trash');?>"><?php echo JText::_('COM_CJFORUM_PMS_TRASH');?></a></li>
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&task=message.add&return='.$return);?>"><?php echo JText::_('COM_CJFORUM_PMS_COMPOSE');?></a></li>
	</ul>
	
	<div class="panel panel-<?php echo $this->theme;?>">
		<?php if(!empty($this->items)):?>
		
		<?php 
		foreach ($this->items as $item)
		{
			$author = $this->escape($item->sender_name);
			$profileUrl = $api->getUserProfileUrl($profileComponent, $item->sender_id);
		?>
		<ul class="list-group no-space-left">
			<li class="list-group-item<?php echo $item->receiver_state != 0 ? '' : ' list-group-item-warning';?>">
				<div class="media clearfix">
					<div class="media-object pull-left margin-right-10 hidden-phone">
						<?php if($profileComponent != 'none'):?>
						<a href="<?php echo $profileUrl;?>" class="thumbnail no-margin-bottom" title="<?php echo $author?>" data-toggle="tooltip">
							<?php echo $api->getUserAvatarImage($avatarComponent, $item->sender_id, $item->sender_email, 24, false, $author);?>
						</a>
						<?php else:?>
						<div class="thumbnail">
							<img alt="<?php echo $author?>" title="<?php echo $author?>" data-toggle="tooltip" 
								src="<?php echo $api->getUserAvatarImage($avatarComponent, $item->sender_id, $item->sender_email, 24);?>">
						</div>
						<?php endif;?>
					</div>
					<div class="media-body">
						<div class="media-heading no-margin-top">
							<a href="<?php echo CjForumHelperRoute::getMessageRoute($item->slug);?>"><strong><?php echo $this->escape($item->title);?></strong></a>
						</div>
						<div class="muted text-muted">
							<?php echo JHtml::link($profileUrl, $author);?> &raquo;
							<?php echo JHtml::_('date', $item->displayDate, JText::_('DATE_FORMAT_LC4'));?> &raquo;
							<?php echo JHTML::_('string.truncate', strip_tags($item->description), $this->params->get('readmore_limit', 180));?>
						</div>
					</div>
				</div>
			</li>
		</ul>
		<?php 
		}
		?>
		
		<?php else:?>
		<div class="panel-body"><?php echo JText::_('COM_CJFORUM_PMS_NO_MESSAGES_FOUND')?></div>
		<?php endif;?>
	</div>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
</div>