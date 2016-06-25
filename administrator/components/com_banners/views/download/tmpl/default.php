<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_banners&task=tracks.display&format=raw'); ?>"
	method="post"
	name="adminForm"
	id="download-form"
	class="form-validate">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_BANNERS_TRACKS_DOWNLOAD'); ?></legend>

		<?php foreach ($this->form->getFieldset() as $field) : ?>
			<?php if ($field->hidden) : ?>
				<div class="control-group">
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php else: ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
		<div class="clr"></div>
		<button type="button" class="btn" onclick="this.form.submit();window.top.setTimeout('window.parent.jQuery(\'#modal-download\').modal(\'hide\')', 700);"><?php echo JText::_('COM_BANNERS_TRACKS_EXPORT'); ?></button>
		<button type="button" class="btn" onclick="window.parent.jQuery('#modal-download').modal('hide');"><?php echo JText::_('COM_BANNERS_CANCEL'); ?></button>

	</fieldset>
</form>
