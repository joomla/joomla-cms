<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * @package    AkeebaBackup
 * @subpackage backuponupdate
 * @copyright Copyright (c)2006-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3, or later
 *
 * @since      5.4.1
 *
 * This file contains the CSS for rendering the status (footer) icon for the Backup on Update plugin. The icon is only
 * rendered in the administrator backend of the site.
 *
 * You can override this file WITHOUT overwriting it. Copy this file into:
 *
 * administrator/templates/YOUR_TEMPLATE/html/plg_system_backuponupdate/default.html.php
 *
 * where YOUR_TEMPLATE is the folder of the administrator template you are using. Modify that copy. It will be loaded
 * instead of the file in plugins/system/backuponupdate.
 */

$token = urlencode(Factory::getSession()->getToken());
$js = <<< JS
; // Work around broken third party Javascript

function akeeba_backup_on_update_toggle()
{
    window.jQuery.get('index.php?_akeeba_backup_on_update_toggle=$token', function() {
        location.reload(true);
    });
}


JS;

$document = Factory::getApplication()->getDocument();

if (empty($document))
{
	$document = Factory::getDocument();
}

if (empty($document))
{
	return;
}

$document->addScriptDeclaration($js);

?>
<div class="btn-group viewsite pull-right" id="akeebaBackupOnUpdateStatusContainer">
    <a href="javascript:akeeba_backup_on_update_toggle()" class="hasPopover"
       data-title="<?php echo Text::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_POPOVER_TITLE') ?>"
       data-content="<p><?php echo Text::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_POPOVER_CONTENT_' . ($params['active'] ? 'ACTIVE' : 'INACTIVE')) ?></p><p class='small'><?php echo Text::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_POPOVER_CONTENT_COMMON') ?></p>"
       data-placement="top">
        <span class="badge badge-<?php echo $params['active'] ? 'success' : 'none' ?>">
            <span class="icon-akeeba-backup-on-update"></span>
        </span>
        <?php echo Text::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_' . ($params['active'] ? 'ACTIVE' : 'INACTIVE')) ?>
    </a>
    <span class="btn-group separator"></span>
</div>
