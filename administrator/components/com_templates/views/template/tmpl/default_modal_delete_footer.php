<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
?>
<form method="post" action="">
	<input type="hidden" name="option" value="com_templates" />
	<input type="hidden" name="task" value="template.delete" />
	<input type="hidden" name="id" value="<?php echo $input->getInt('id'); ?>" />
	<input type="hidden" name="file" value="<?php echo $this->file; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	<button type="button" class="btn" data-dismiss="modal"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?></button>
	<button type="submit" class="btn btn-danger"><?php echo JText::_('COM_TEMPLATES_BUTTON_DELETE'); ?></button>
</form>
