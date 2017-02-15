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
<div id="template-manager-rename" class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="control-group">
				<div class="control-label">
					<label for="new_name" class="modalTooltip" title="<?php echo JHtml::_('tooltipText', JText::_('COM_TEMPLATES_NEW_FILE_NAME')); ?>">
						<?php echo JText::_('COM_TEMPLATES_NEW_FILE_NAME')?>
					</label>
				</div>
				<div class="controls">
					<div class="input-append">
						<input class="input-xlarge" type="text" name="new_name" required />
						<span class="add-on">.<?php echo JFile::getExt($this->fileName); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
