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

$input = Factory::getApplication()->getInput();
?>
<div id="#template-manager-folder" class="container-fluid">
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
                <form method="post" action="<?php echo Route::_('index.php?option=com_templates&task=template.createFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
                    <div class="form-group">
                        <label for="folder_name"><?php echo Text::_('COM_TEMPLATES_FOLDER_NAME'); ?></label>
                        <input type="text" name="name" id="folder_name" class="form-control" required>
                        <input type="hidden" class="address" name="address">
                        <input type="hidden" name="isMedia" value="0">
                        <?php echo HTMLHelper::_('form.token'); ?>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_TEMPLATES_BUTTON_CREATE'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
