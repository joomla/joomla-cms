<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="template-manager-resize" class="form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<label for="height" class="modalTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_TEMPLATES_IMAGE_HEIGHT'); ?>">
				<?php echo JText::_('COM_TEMPLATES_IMAGE_HEIGHT')?>
			</label>
		</div>
		<div class="controls">
			<input class="input-xlarge" type="number" name="height" placeholder="<?php echo $this->image['height']; ?> px" required />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label for="width" class="modalTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_TEMPLATES_IMAGE_WIDTH'); ?>">
				<?php echo JText::_('COM_TEMPLATES_IMAGE_WIDTH')?>
			</label>
		</div>
		<div class="controls">
			<input class="input-xlarge" type="number" name="width" placeholder="<?php echo $this->image['width']; ?> px" required />
		</div>
	</div>
</div>
