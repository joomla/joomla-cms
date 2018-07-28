<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;

?>
<div id="template-manager-rename" class="container-fluid">
	<div class="mt-2">
		<div class="col-md-12">
			<div class="control-group">
				<div class="control-label">
					<label for="new_name" class="modalTooltip" title="<?php echo HTMLHelper::_('tooltipText', Text::_('COM_TEMPLATES_NEW_FILE_NAME')); ?>">
						<?php echo Text::_('COM_TEMPLATES_NEW_FILE_NAME')?>
					</label>
				</div>
				<div class="controls">
					<div class="input-group">
						<input class="form-control" type="text" name="new_name" required>
						<span class="input-group-append">
							<span class="input-group-text">.<?php echo File::getExt($this->fileName); ?></span>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
