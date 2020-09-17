<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();
?>

{{-- Filesystem browser --}}
<div class="modal" id="folderBrowserDialog" tabindex="-1" role="dialog" aria-labelledby="folderBrowserDialogLabel"
     aria-hidden="true" style="display: none;">
    <div class="akeeba-renderer-fef">
        <h4 id="folderBrowserDialogLabel">
		    @lang('COM_AKEEBA_CONFIG_UI_BROWSER_TITLE')
        </h4>
        <div id="folderBrowserDialogBody">
        </div>
    </div>
</div>
