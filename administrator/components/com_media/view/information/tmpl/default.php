<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;
$editing = $input->get('editing', '', 'STRING');
?>



<form action="<?php echo JRoute::_('index.php?option=com_media&controller=save&editing=' . $editing); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">

	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_MEDIA_MEDIA_DETAILS', true)); ?>
			<fieldset class="adminform">
				<div class="control-group form-inline">
					<?php echo $this->form->getLabel('title'); ?> <?php echo $this->form->getInput('title'); ?> <?php echo $this->form->getLabel('catid'); ?> <?php echo $this->form->getInput('catid'); ?>
				</div>
				<div>
					<a href="#"><img src="<?php echo JURI::root() . "images/" . $editing ?>" style="max-height: 300px"/></a>
				</div>
				<div style="margin-top:10px">
					<button class="btn" type="button" id="editImage" onclick="window.location='<?php echo JURI::base()?>/index.php?option=com_media&controller=edit&operation=edit&editing=<?php echo $editing?>'; return false">Edit Image</button>
				</div>
			</fieldset>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_MEDIA_FIELDSET_PUBLISHING', true)); ?>
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<?php echo $this->form->getLabel('alias'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('alias'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('id'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('created_by'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('created_by'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('created_by_alias'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('created_by_alias'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('created'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('created'); ?>
						</div>
					</div>
				</div>
				<div class="span6">
						<div class="control-group">
							<?php echo $this->form->getLabel('modified_by'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('modified_by'); ?>
							</div>
						</div>
						<div class="control-group">
							<?php echo $this->form->getLabel('modified'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('modified'); ?>
							</div>
						</div>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php if ($this->canDo->get('core.admin')) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_MEDIA_FIELDSET_RULES', true)); ?>
				<fieldset>
					<?php echo $this->form->getInput('rules'); ?>
				</fieldset>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="operation" id="operation" value=""/>
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
		<!-- Begin Sidebar -->
		<?php echo JLayoutHelper::render('joomla.edit.new_details', $this); ?>
		<!-- End Sidebar -->
	</div>
</form>

<script>
	jQuery(function($)
	{
		$(document).ready(function()
		{
			$("#apply, #save, #close").click(function(){
				$("#operation").val($(this).attr('id'));
				$("#item-form").submit();
			})
			$("#editImage").unbind();
		});
	});
</script>
