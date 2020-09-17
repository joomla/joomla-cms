<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();
?>
{{--  Error modal  --}}
<div id="errorDialog" tabindex="-1" role="dialog" aria-labelledby="errorDialogLabel" aria-hidden="true"
     style="display:none;">
    <div class="akeeba-renderer-fef">
        <h4 id="errorDialogLabel">
			@lang('COM_AKEEBA_CONFIG_UI_AJAXERRORDLG_TITLE')
        </h4>

        <p>
			@lang('COM_AKEEBA_CONFIG_UI_AJAXERRORDLG_TEXT')
        </p>
        <pre id="errorDialogPre"></pre>
    </div>
</div>
