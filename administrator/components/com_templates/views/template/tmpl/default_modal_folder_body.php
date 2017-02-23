<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
?>
<div id="#template-manager-folder" class="container-fluid">
	<div class="mt-2">
		<div class="col-md-12">
			<div class="col-md-6 column-right">
				<form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.createFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" class="well">
					<fieldset class="form-inline">
						<label><?php echo JText::_('COM_TEMPLATES_FOLDER_NAME'); ?></label>
						<input type="text" name="name" required>
						<input type="hidden" class="address" name="address">
						<?php echo JHtml::_('form.token'); ?>
						<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_CREATE'); ?>" class="btn btn-primary">
					</fieldset>
				</form>
			</div>
			<div class="col-md-6 column-left">
				<?php echo $this->loadTemplate('folders'); ?>
				<hr class="hr-condensed" />
			</div>
		</div>
	</div>
</div>
