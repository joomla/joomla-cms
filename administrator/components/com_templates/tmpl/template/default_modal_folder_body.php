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

$input = Factory::getApplication()->input;
?>
<div id="#template-manager-folder" class="container-fluid">
	<div class="mt-2 p-2">
		<div class="row">
			<div class="col-md-4">
				<div class="tree-holder">
					<?php echo $this->loadTemplate('folders'); ?>
				</div>
			</div>
			<div class="col-md-8">
				<form method="post" action="<?php echo Route::_('index.php?option=com_templates&task=template.createFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
					<div class="form-group">
						<label for="folder_name"><?php echo Text::_('COM_TEMPLATES_FOLDER_NAME'); ?></label>
						<input type="text" name="name" id="folder_name" class="form-control" required>
						<input type="hidden" class="address" name="address">
						<?php echo HTMLHelper::_('form.token'); ?>
					</div>
					<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_TEMPLATES_BUTTON_CREATE'); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
