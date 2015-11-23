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
JHtml::_('behavior.keepalive');
JHtml::_('script', 'system/core.js', false, true);

$app				= JFactory::getApplication();
$user    			= JFactory::getUser();
$api				= new CjLibApi();

$microdata 			= new JMicrodata('Comment');
$params  			= $this->item->params;
$layout 			= $params->get('layout', 'default');
$start				= $app->input->getInt('start', 0);
$topic_uri			= CjForumHelperRoute::getTopicRoute($this->item->slug, $this->item->catslug, $this->item->language, $start);

$params->set('catid', $this->item->catid);
?>
<div id="cj-wrapper" class="topic-details<?php echo $this->pageclass_sfx?>">

	<?php // ************************* PAGE HEADER ***************************//
	echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$params, 'state'=>$this->state));
	if($this->item->locked == 1)
	{
		?>
		<div class="alert alert-info"><?php echo JText::_('COM_CJFORUM_TOPIC_IS_LOCKED');?></div>
		<?php
	}
	
	if ($params->get('show_page_heading', 1))
	{
		?>
		<div class="page-header"><h1> <?php echo $this->escape($params->get('page_heading')); ?> </h1></div>
		<?php
	}
	// *************************** END PAGE HEADER ****************************//
	?>
	
	<?php // ************************* TOPIC BODY *****************************// ?>
	<div class="main-content-wrap topic-body content-block">
		<?php echo $this->loadTemplate('topic');?>
	</div>
	<?php // *********************** END TOPIC BODY ***************************// ?>
	
	<?php // ************************ TOPIC REPLIES ***************************// ?>
	<ul class="list-group no-margin-left replies">
		<?php 
		if(!empty($this->replies))
		{
			foreach ($this->replies as $i=>$reply)
			{
				?>
				<li class="list-group-item<?php echo $reply->state != 1 ? ' list-group-item-danger' : '';?> no-margin-left" 
					id="p<?php echo $reply->id;?>" <?php echo $microdata->property('comment')->fallback('UserInteraction', 'UserComments')->display();?>>
					<?php 
					$this->reply = &$reply;
					echo $this->loadTemplate('reply');
					echo CJFunctions::load_module_position('topic-view-after-reply-'.($i+1));
					?>
				</li>
				<?php 
			}
		}
		else
		{
			?>
			<li class="list-group-item"><?php echo JText::_('COM_CJFORUM_NO_REPLIES');?></li>
			<?php 
		}
		?>
	</ul>
	<?php // ********************* END TOPIC REPLIES ***************************// ?>
	
	<?php // ************************* PAGINATION ******************************// ?>
	<?php if (($params->def('show_pagination', 2) == 1  || ($params->get('show_pagination') == 2)) && !empty($this->replies) && ($this->pagination->pagesTotal > 1)) : ?>
	<div class="clearfix">
		<div class="pagination">
	
			<?php if ($params->def('show_pagination_results', 1)) : ?>
				<p class="counter pull-right">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</p>
			<?php endif; ?>

			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	</div>
	<?php endif; ?>
	<?php // ********************** END PAGINATION ******************************// ?>
	
	<?php // ************************* REPLY FORM ******************************// ?>
	<div class="reply-form">
		<form action="<?php echo JRoute::_('index.php?option=com_cjforum'); ?>" name="adminForm" id="adminForm" method="post">
		
			<?php if($this->item->locked != 1 && $this->item->params->get('access-reply')):?>
			<h3 class="page-header no-space-top"><?php echo JText::_('COM_CJFORUM_POST_YOUR_REPLY');?></h3>
			<div class="form-group clearfix"><?php echo $this->form->getInput('description'); ?></div>
			<div class="panel-footer margin-top-10 clearfix">
				<div class="checkbox pull-left">
					<label for="subscribe">
						<input type="checkbox" name="subscribe" id="subscribe" value="1"> <?php echo JText::_('COM_CJFORUM_SUBSCRIBE');?>
					</label>
				</div>
				<button type="button" class="btn btn-primary pull-right" onclick="Joomla.submitbutton('reply.save')">
					<span class="fa fa-reply"></span>&#160;<?php echo JText::_('COM_CJFORUM_POST_REPLY') ?>
				</button>
			</div>
			<?php endif;?>
			
			<input type="hidden" name="t_id" value="<?php echo $this->item->id;?>">
			<input type="hidden" name="cid" value="">
			<input type="hidden" name="r_id" value="">
			<input type="hidden" name="d_id" value="">
			<input type="hidden" name="quote" value="0">
			<input type="hidden" name="catid" value="<?php echo $this->item->catid;?>">
			<input type="hidden" name="jform[topic_id]" value="<?php echo $this->item->id;?>">
			<input type="hidden" name="jform[catid]" value="<?php echo $this->item->catid;?>">
			<input type="hidden" name="task" value="reply.save">
			<input type="hidden" name="view" value="topics">
			<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_($topic_uri.($this->item->page_start > 0 ? '&start='.$this->item->page_start : '')));?>">
			<input type="hidden" name="cjforum_pageid" id="cjforum_pageid" value="topic">
			<?php echo JHtml::_('form.token'); ?>
		</form>
		
		<?php if($this->item->locked == 1 || ! $this->item->params->get('access-reply')):?>
			<?php if($this->item->params->get('max_replies_limit_reached', false)):?>
			<div class="alert alert-info"><?php echo JText::_('COM_CJFORUM_ERROR_MAX_REPLIES_LIMIT_REACHED');?></div>
			<?php else :?>
			<div class="alert alert-info"><?php echo JText::_('COM_CJFORUM_NO_REPLY_ACCESS');?></div>
			<?php endif;?>
		<?php endif;?>
	</div>
	<?php // *********************** END REPLY FORM ***************************// ?>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
</div>