<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
?>
<div class="column">
	<?php echo $this->loadTemplate('folders');?>
</div>
<div class="column">
	<form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.createFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" class="well">
		<fieldset>
			<label><?php echo JText::_('COM_TEMPLATES_FOLDER_NAME');?></label>
			<input type="text" name="name" required />
			<input type="hidden" class="address" name="address" />
			<?php echo JHtml::_('form.token'); ?>
			<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_CREATE');?>" class="btn btn-primary" />
		</fieldset>
	</form>
</div>
