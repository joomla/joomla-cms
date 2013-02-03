<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_banners&task=tracks.display&format=raw');?>"
	method="post"
	name="adminForm"
	id="download-form"
	class="form-validate">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_BANNERS_TRACKS_DOWNLOAD');?></legend>

		<?php foreach($this->form->getFieldset() as $field): ?>
			<?php if (!$field->hidden): ?>
				<?php echo $field->label; ?>
			<?php endif; ?>
			<?php echo $field->input; ?>
		<?php endforeach; ?>
		<div class="clr"></div>
		<button type="button" onclick="this.form.submit();window.top.setTimeout('window.parent.SqueezeBox.close()', 700);"><?php echo JText::_('COM_BANNERS_TRACKS_EXPORT');?></button>
		<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('COM_BANNERS_CANCEL');?></button>

	</fieldset>
</form>
