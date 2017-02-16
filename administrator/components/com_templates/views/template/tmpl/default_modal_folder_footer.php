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
<form id="deleteFolder" method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.deleteFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
	<fieldset>
		<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?></a>
		<input type="hidden" class="address" name="address" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_DELETE'); ?>" class="btn btn-danger" />
	</fieldset>
</form>
