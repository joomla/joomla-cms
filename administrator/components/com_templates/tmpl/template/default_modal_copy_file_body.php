<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Templates\Administrator\View\Template\HtmlView $this */

$input = Factory::getApplication()->getInput();
?>
<div id="template-manager-copy-file" class="container-fluid">
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
                <div class="form-group">
                    <label for="copy_file_name">
                        <?php echo Text::_('COM_TEMPLATES_FILE_NEW_NAME_LABEL'); ?>
                    </label>
                    <input class="form-control" type="text" id="copy_file_name" name="new_name" required>
                    <input type="hidden" class="address" name="address">
                    <input type="hidden" name="isMedia" value="0">
                </div>
            </div>
        </div>
    </div>
</div>
