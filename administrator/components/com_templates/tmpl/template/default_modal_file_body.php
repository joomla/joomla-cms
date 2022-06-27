<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
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
			<div class="col-md-4">
				<div class="tree-holder">
					<ul class="directory-tree treeselect root">
						<li class="folder-select">
							<a class="folder-url" data-id="" href="" data-base="template">
								<span class="icon-folder icon-fw" aria-hidden="true"></span>
								<?php echo ($this->template->client_id === 0 ? '/' : '/administrator/') . 'templates/' . $this->template->element; ?>
							</a>
							<?php echo $this->loadTemplate('folders'); ?>
						</li>
					</ul>
					<?php if (count($this->mediaFiles)) : ?>
						<ul class="directory-tree treeselect">
							<li class="folder-select">
								<a class="folder-url" data-id="" href="" data-base="media">
									<span class="icon-folder icon-fw" aria-hidden="true"></span>
									<?php echo '/media/templates/' . ($this->template->client_id === 0 ? 'site/' : 'administrator/') . $this->template->element; ?>
								</a>
								<?php echo $this->loadTemplate('media_folders'); ?>
							</li>
						</ul>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-md-8">
				<form method="post" action="<?php echo Route::_('index.php?option=com_templates&task=template.createFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" class="mb-4">
					<div class="form-group">
						<label for="file_name"><?php echo Text::_('COM_TEMPLATES_FILE_NAME'); ?></label>
						<input type="text" name="name" id="file_name" class="form-control" required>
					</div>
					<div class="form-group">
						<label for="type"><?php echo Text::_('COM_TEMPLATES_NEW_FILE_TYPE'); ?></label>
						<select class="form-select" data-chosen="true" name="type" id="type" required>
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
					<input type="hidden" name="isMedia" value="0">
					<?php echo HTMLHelper::_('form.token'); ?>
					<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_TEMPLATES_BUTTON_CREATE'); ?></button>
				</form>
				<hr class="mb-4">
				<form method="post" action="<?php echo Route::_('index.php?option=com_templates&task=template.uploadFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" enctype="multipart/form-data" class="mb-4">
					<input type="hidden" class="address" name="address">
					<input type="hidden" name="isMedia" value="0">
					<div class="input-group">
						<input type="file" name="files" aria-labelledby="upload" class="form-control" required>
						<?php echo HTMLHelper::_('form.token'); ?>
						<button type="submit" class="btn btn-primary" id="upload"><?php echo Text::_('COM_TEMPLATES_BUTTON_UPLOAD'); ?></button>
					</div>
					<?php $cMax    = $this->state->get('params')->get('upload_limit'); ?>
					<?php $maxSize = HTMLHelper::_('number.bytes', Utility::getMaxUploadSize($cMax . 'MB')); ?>
					<span class="mt-2"><?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', '&#x200E;' . $maxSize); ?></span>
				</form>
				<?php if ($this->type != 'home') : ?>
					<hr class="mb-4">
					<form method="post" action="<?php echo Route::_('index.php?option=com_templates&task=template.copyFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" enctype="multipart/form-data" class="mb-4">
						<div class="form-group">
							<input type="hidden" class="address" name="address">
							<input type="hidden" name="isMedia" value="0">
							<label for="new_name">
								<?php echo Text::_('COM_TEMPLATES_FILE_NEW_NAME_LABEL') ?>
							</label>
							<input class="form-control" type="text" id="new_name" name="new_name" required>
							<?php echo HTMLHelper::_('form.token'); ?>
						</div>
						<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_TEMPLATES_BUTTON_COPY_FILE'); ?></button>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
