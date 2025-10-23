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
use Joomla\Component\Admin\Administrator\View\Help\ToC;

/** @var \Joomla\Component\Admin\Administrator\View\Help\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_admin.admin-help');
$wa->addInlineStyle('
    #help-sidebar {
        min-width: 18rem;
    }
    #help-index.main-nav {
        inline-size: 18rem;
        max-inline-size: 18rem;
    }
    #help-index ul, #help-index li {
        inline-size: 18rem;
        max-inline-size: 18rem;
    }
    #helpmenu .has-arrow .item-title {
        margin-inline-end: auto;
    }
    #helpmenu .has-arrow::after  {
        display: flex;
    }
    .closed #helpmenu a:hover {
        max-inline-size: 18rem;
    }
    #help-index.sidebar-wrapper .item-level-2 > a {
        padding-inline-start: 1rem;
    }
    #help-index.sidebar-wrapper .item-level-3 > a {
        padding-inline-start: 1.5rem;
    }
    .help-nav .mm-collapse {
        display: none;
    }
    .help-nav .mm-collapse.mm-show {
        display: block;
    }
');

// Get the HTML for the Table of Contents from a separate file.
require_once 'toc-build.php';
$tocBuilder =  new Toc();
$toc = $tocBuilder->getToc();

?>
<div class="d-flex flex-column flex-md-row">
    <div id="help-sidebar" class="flex-shrink-0">
        <!-- Left menu -->
        <button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#help-index" aria-controls="help-index" aria-expanded="false">
            <span class="icon-align-justify" aria-hidden="true"></span>
            <?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>
        </button>
        <h2><?php echo Text::_('COM_ADMIN_HELP_INDEX'); ?></h2>
        <div id="help-index" class="main-nav sidebar-wrapper">
            <ul id="helpmenu" class="help-nav flex-column">
                <?php echo $toc; ?>
            </ul>
        </div>
    </div>
    <div class="flex-grow-1 p-3">
        <!-- Right content -->
        <iframe name="helpFrame" title="helpFrame" height="2100px" src="" class="helpFrame table table-bordered"></iframe>
    </div>
</div>
