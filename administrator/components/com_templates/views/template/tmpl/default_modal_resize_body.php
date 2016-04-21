<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="template-manager-css" class="form-horizontal">
	<div class="control-group">
		<label for="height" class="control-label hasTooltip" title="<?php echo JHtml::tooltipText('COM_TEMPLATES_IMAGE_HEIGHT'); ?>"><?php echo JText::_('COM_TEMPLATES_IMAGE_HEIGHT')?></label>
		<div class="controls">
			<input class="input-xlarge" type="number" name="height" placeholder="<?php echo $this->image['height']; ?> px" required />
		</div>
		<br />
		<label for="width" class="control-label hasTooltip" title="<?php echo JHtml::tooltipText('COM_TEMPLATES_IMAGE_WIDTH'); ?>"><?php echo JText::_('COM_TEMPLATES_IMAGE_WIDTH')?></label>
		<div class="controls">
			<input class="input-xlarge" type="number" name="width" placeholder="<?php echo $this->image['width']; ?> px" required />
		</div>
	</div>
</div>
