<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div id="template-manager-resize" class="container-fluid">
	<div class="mt-2">
		<div class="col-md-12">
			<div class="control-group">
				<div class="control-label">
					<label for="height">
						<?php echo Text::_('COM_TEMPLATES_IMAGE_HEIGHT')?>
					</label>
				</div>
				<div class="controls">
					<input class="form-control" type="number" name="height" id="height" placeholder="<?php echo $this->image['height']; ?> px" required>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="width">
						<?php echo Text::_('COM_TEMPLATES_IMAGE_WIDTH')?>
					</label>
				</div>
				<div class="controls">
					<input class="form-control" type="number" name="width" id="width" placeholder="<?php echo $this->image['width']; ?> px" required>
				</div>
			</div>
		</div>
	</div>
</div>
