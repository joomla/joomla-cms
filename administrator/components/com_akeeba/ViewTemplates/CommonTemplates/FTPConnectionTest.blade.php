<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();
?>
{{-- (S)FTP connection test --}}
<div class="modal fade" id="testFtpDialog" tabindex="-1" role="dialog" aria-labelledby="testFtpDialogLabel"
     aria-hidden="true" style="display:none;">
    <div class="akeeba-renderer-fef">
        <h4 class="modal-title" id="testFtpDialogLabel"></h4>
        <div class="akeeba-block--success" id="testFtpDialogBodyOk"></div>
        <div class="akeeba-block--failure" id="testFtpDialogBodyFail"></div>
    </div>
</div>
