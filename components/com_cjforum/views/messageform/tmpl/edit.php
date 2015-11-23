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
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

$profileComponent = $this->params->get('avatar_component', 'cjforum');
$avatarComponent = $this->params->get('user_avatar', 'cjforum');
$layout = $this->params->get('layout', 'default');

$app = JFactory::getApplication();
$replyTo = $app->input->getInt('replyto', 0);
$api = new CjLibApi();
?>
<div id="cj-wrapper" class="pms <?php echo $this->pageclass_sfx;?>">

	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));?>
	
	<ul class="nav nav-tabs margin-bottom-5" role="tablist">
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages');?>"><?php echo JText::_('COM_CJFORUM_PMS_INBOX');?></a></li>
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages&layout=sent');?>"><?php echo JText::_('COM_CJFORUM_PMS_SENT');?></a></li>
		<li><a href="<?php echo JRoute::_('index.php?option=com_cjforum&view=messages&layout=trash');?>"><?php echo JText::_('COM_CJFORUM_PMS_TRASH');?></a></li>
		<li class="active"><a href="<?php echo JRoute::_('index.php?option=com_cjforum&task=message.edit');?>"><?php echo JText::_('COM_CJFORUM_PMS_COMPOSE');?></a></li>
	</ul>

	<form action="<?php echo JRoute::_('index.php?option=com_cjforum'); ?>" name="adminForm" id="adminForm" method="post">
		<h3 class="page-header"><?php echo JText::_('COM_CJFORUM_COMPOSE_MESSAGE');?></h3>
		
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('title'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
			</div>
		</div>
		
		<div class="control-group margin-bottom-20">
			<div class="control-label">
				<?php echo $this->form->getLabel('userIds'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('userIds'); ?>
			</div>
		</div>
		
		<div class="form-group">
			<?php echo $this->form->getInput('description'); ?>
		</div>
		
		<div class="panel panel-<?php echo $this->params->get('theme', 'default');?> center">
			<div class="panel-body">
				<div class="btn-group">
					<button type="button" class="btn" onclick="Joomla.submitbutton('message.cancel')">
						<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL')?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('message.save')">
						<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE')?>
					</button>
				</div>
			</div>
		</div>
		
		<?php echo $this->form->getInput('parent_id'); ?>
		<input type="hidden" name="m_id" value="<?php echo $this->item->id;?>">
		<input type="hidden" name="replyto" value="<?php echo $this->state->get('messageform.parent_id');?>">
		<input type="hidden" name="cid" value="">
		<input type="hidden" name="quote" value="0">
		<input type="hidden" name="task" value="message.save">
		<input type="hidden" name="view" value="messages">
		<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
</div>