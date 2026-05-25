<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Admin\Administrator\View\Help\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_admin.admin-help')
    ->useStyle('com_admin.admin-help');

// Get the HTML for the Table of Contents from a separate file.
include_once 'toc-src.php';

?>
<div class="d-flex flex-column flex-md-row">
    <div id="help-sidebar" class="flex-shrink-0 mt-md-2">
        <!-- Left menu -->
        <button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#help-index" aria-controls="help-index" aria-expanded="false">
            <span class="icon-align-justify" aria-hidden="true"></span>
            <?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>
        </button>
        <nav id="help-index" class="main-nav help-nav sidebar-wrapper">
            <h2><?php echo Text::_('COM_ADMIN_HELP_INDEX'); ?></h2>
            <ul id="helpmenu" class="help-nav flex-column">
                <?php
                    // WARNING: Do not use direct 'include' or 'require' as it is important to isolate the scope for each call
                    $this->renderSubmenu(JPATH_ADMINISTRATOR . '/components/com_admin/tmpl/help/toc-build.php', $menu);
                ?>
            </ul>
        </nav>
    </div>
    <div class="flex-grow-1 mt-2">
        <!-- Right content -->
        <iframe name="helpFrame" title="<?php echo Text::_('COM_ADMIN_HELP_FRAME_TITLE'); ?>" height="2100px" src="" class="helpFrame table table-bordered"></iframe>
    </div>
</div>
