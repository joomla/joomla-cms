<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
?>
<div id="template-manager-file" class="container-fluid">
	<div class="row-fluid">
		<div class="span12">
			<div class="span6 column-right">
				<form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.createFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" class="well">
					<fieldset class="form-inline">
						<label><?php echo JText::_('COM_TEMPLATES_FILE_NAME'); ?></label>
						<input type="text" name="name" required />
						<select class="input-medium" data-chosen="true" name="type" required >
							<option value="">- <?php echo JText::_('COM_TEMPLATES_NEW_FILE_SELECT'); ?> -</option>
							<option value="css">css</option>
							<option value="php">php</option>
							<option value="js">js</option>
							<option value="xml">xml</option>
							<option value="ini">ini</option>
							<option value="less">less</option>
							<option value="sass">sass</option>
							<option value="scss">scss</option>
							<option value="txt">txt</option>
						</select>
						<input type="hidden" class="address" name="address" />
						<?php echo JHtml::_('form.token'); ?>
						<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_CREATE'); ?>" class="btn btn-primary" />
					</fieldset>
				</form>
				<form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.uploadFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" class="well" enctype="multipart/form-data">
					<fieldset class="form-inline">
						<input type="hidden" class="address" name="address" />
						<input type="file" name="files" required />
						<?php echo JHtml::_('form.token'); ?>
						<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_UPLOAD'); ?>" class="btn btn-primary" /><br>
						<?php $cMax    = $this->state->get('params')->get('upload_limit'); ?>
						<?php $maxSize = JHtml::_('number.bytes', JUtility::getMaxUploadSize($cMax . 'MB')); ?>
						<?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
					</fieldset>
				</form>
				<?php if ($this->type != 'home') : ?>
					<form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.copyFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" class="well" enctype="multipart/form-data">
						<fieldset class="form-inline">
							<input type="hidden" class="address" name="address" />
							<label for="new_name" class="modalTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_TEMPLATES_FILE_NEW_NAME_DESC'); ?>">
								<?php echo JText::_('COM_TEMPLATES_FILE_NEW_NAME_LABEL')?>
							</label>
							<input type="text" id="new_name" name="new_name" required />
							<?php echo JHtml::_('form.token'); ?>
							<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_COPY_FILE'); ?>" class="btn btn-primary" />
						</fieldset>
					</form>
				<?php endif; ?>
			</div>
			<div class="span6 column-left">
				<?php echo $this->loadTemplate('folders'); ?>
				<hr class="hr-condensed" />
			</div>
		</div>
	</div>
</div>
