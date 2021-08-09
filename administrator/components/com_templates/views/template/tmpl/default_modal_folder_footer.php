<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
?>
<form id="deleteFolder" method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.deleteFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
	<fieldset>
		<button type="button" class="btn" data-dismiss="modal"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?></button>
		<input type="hidden" class="address" name="address" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_DELETE'); ?>" class="btn btn-danger" />
	</fieldset>
</form>
