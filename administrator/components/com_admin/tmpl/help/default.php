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
use Joomla\Component\Admin\Administrator\View\Help\Toc;

/** @var \Joomla\Component\Admin\Administrator\View\Help\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_admin.admin-help')
    ->useStyle('com_admin.admin-help');

// Get the HTML for the Table of Contents from a separate file.
require_once 'toc-build.php';
$tocBuilder =  new Toc();
$toc = $tocBuilder->getToc();

?>
<div class="d-flex flex-column flex-md-row">
    <div id="help-sidebar" class="flex-shrink-0 mt-md-2">
        <!-- Left menu -->
        <button class="btn btn-sm btn-secondary my-md-2 options-menu d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#help-index" aria-controls="help-index" aria-expanded="false">
            <span class="icon-align-justify" aria-hidden="true"></span>
            <?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>
        </button>
        <nav id="help-index" class="main-nav sidebar-wrapper">
            <h2><?php echo Text::_('COM_ADMIN_HELP_INDEX'); ?></h2>
            <ul id="helpmenu" class="help-nav flex-column pt-2">
                <?php echo $toc; ?>
            </ul>
        </nav>
    </div>
    <div class="flex-grow-1 mt-2">
        <!-- Right content -->
        <iframe name="helpFrame" title="<?php echo Text::_('COM_ADMIN_HELP_FRAME_TITLE'); ?>" height="2100px" src="" class="helpFrame table table-bordered"></iframe>
    </div>
</div>
