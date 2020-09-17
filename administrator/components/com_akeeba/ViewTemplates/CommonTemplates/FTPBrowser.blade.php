<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();
?>
<?php /* FTP browser */ ?>
<div class="modal fade" id="ftpdialog" tabindex="-1" role="dialog" aria-labelledby="ftpdialogLabel" aria-hidden="true"
     style="display: none;">
    <div class="akeeba-renderer-fef">
        <h4 id="ftpdialogLabel">
		    @lang('COM_AKEEBA_CONFIG_UI_FTPBROWSER_TITLE')
        </h4>

        <p class="instructions akeeba-block--info">
		    @lang('COM_AKEEBA_FTPBROWSER_LBL_INSTRUCTIONS')
        </p>
        <div class="error akeeba-block--failure" id="ftpBrowserErrorContainer">
            <h3>@lang('COM_AKEEBA_FTPBROWSER_LBL_ERROR')</h3>
            <p id="ftpBrowserError"></p>
        </div>

        <ul id="ak_crumbs2" class="breadcrumb"></ul>

        <div class="folderBrowserWrapper" id="ftpBrowserWrapper">
            <table id="ftpBrowserFolderList" class="akeeba-table akeeba-table--striped">
            </table>
        </div>

        <div>
            <button type="button" id="ftpdialogOkButton" class="akeeba-btn--primary">
                <span class="akion-checkmark"></span>
		        @lang('COM_AKEEBA_BROWSER_LBL_USE')
            </button>

            <button type="button" id="ftpdialogCancelButton" class="akeeba-btn--red">
                <span class="akion-ios-close"></span>
		        @lang('JTOOLBAR_CANCEL')
            </button>
        </div>
    </div>
</div>
