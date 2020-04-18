<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Utility\Utility;

$input = Factory::getApplication()->input;
?>
<div id="#template-manager-file" class="container-fluid">
	<div class="mt-2 p-2">
		<div class="row">
			<div class="col-md-6">
				<div class="tree-holder">
					<?php echo $this->loadTemplate('folders'); ?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card card-outline-secondary mb-2">
					<div class="card-body">
						<form method="post" action="<?php echo Route::_('index.php?option=com_templates&task=template.createFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
							<div class="form-group">
								<label><?php echo Text::_('COM_TEMPLATES_FILE_NAME'); ?></label>
								<input type="text" name="name" class="form-control" required>
							</div>
							<div class="form-group">
								<select class="custom-select" data-chosen="true" name="type" required >
									<option value="">- <?php echo Text::_('COM_TEMPLATES_NEW_FILE_SELECT'); ?> -</option>
									<option value="css">.css</option>
									<option value="php">.php</option>
									<option value="js">.js</option>
									<option value="xml">.xml</option>
									<option value="ini">.ini</option>
									<option value="less">.less</option>
									<option value="sass">.sass</option>
									<option value="scss">.scss</option>
									<option value="txt">.txt</option>
								</select>
							</div>
							<input type="hidden" class="address" name="address">
							<?php echo HTMLHelper::_('form.token'); ?>
							<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_TEMPLATES_BUTTON_CREATE'); ?></button>
						</form>
					</div>
				</div>
				<div class="card card-outline-secondary mb-2">
					<div class="card-body">
						<form method="post" action="<?php echo Route::_('index.php?option=com_templates&task=template.uploadFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" enctype="multipart/form-data">
							<input type="hidden" class="address" name="address">
							<div class="input-group">
								<input type="file" name="files" class="form-control" required>
								<?php echo HTMLHelper::_('form.token'); ?>
								<span class="input-group-append">
									<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_TEMPLATES_BUTTON_UPLOAD'); ?></button>
								</span>
							</div>
							<?php $cMax    = $this->state->get('params')->get('upload_limit'); ?>
							<?php $maxSize = HTMLHelper::_('number.bytes', Utility::getMaxUploadSize($cMax . 'MB')); ?>
							<span class="mt-2"><?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', '&#x200E;' . $maxSize); ?></span>
						</form>
					</div>
				</div>
				<?php if ($this->type != 'home') : ?>
				<div class="card card-outline-secondary mb-2">
					<div class="card-body">
						<form method="post" action="<?php echo Route::_('index.php?option=com_templates&task=template.copyFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" enctype="multipart/form-data">
							<div class="form-group">
								<input type="hidden" class="address" name="address">
								<label for="new_name">
									<?php echo Text::_('COM_TEMPLATES_FILE_NEW_NAME_LABEL')?>
								</label>
								<input class="form-control" type="text" id="new_name" name="new_name" required>
								<?php echo HTMLHelper::_('form.token'); ?>
							</div>
							<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_TEMPLATES_BUTTON_COPY_FILE'); ?></button>
						</form>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
