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
 * @copyright  Copyright (c)2006-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3, or later
 *
 * @since      6.4.1
 *
 * This file contains the CSS for rendering the status (footer) icon for the Backup on Update plugin. The icon is only
 * rendered in the administrator backend of the site.
 *
 * You can override this file WITHOUT overwriting it. Copy this file into:
 *
 * administrator/templates/YOUR_TEMPLATE/html/plg_system_backuponupdate/joomla4.html.php
 *
 * where YOUR_TEMPLATE is the folder of the administrator template you are using. Modify that copy. It will be loaded
 * instead of the file in plugins/system/backuponupdate.
 */

$document = Factory::getApplication()->getDocument();

if (empty($document))
{
	return;
}

$document->addScript('../media/com_akeeba/js/System.min.js');

$token = urlencode(Factory::getSession()->getToken());
$js    = <<< JS
; // Work around broken third party Javascript

function akeeba_backup_on_update_toggle()
{
    window.jQuery.get('index.php?_akeeba_backup_on_update_toggle=$token', function() {
        location.reload(true);
    });
}

akeeba.System.documentReady(function() {
    var myItem = document.getElementById('plg_system_backuponupdate');
    if (typeof myItem == "undefined") return;
    var myContainer = myItem.parentElement;
    if (typeof myContainer == "undefined") return;
    var headerIconsContainer = myContainer.parentElement.parentElement;
    if (typeof headerIconsContainer == "undefined") return;
    var headerIcons = headerIconsContainer.querySelectorAll('div.header-item');
    if (typeof headerIcons == "undefined") return;
    
    if (headerIcons.length < 2)
	{
		headerIconsContainer.appendChild(myItem);
	}
    else
	{
		headerIconsContainer.insertBefore(myItem, headerIcons[1]);
	}
    
    try {
        headerIconsContainer.removeChild(myContainer);
    } catch (e) {}
})

JS;

$document->addScriptDeclaration($js);

?>
<div class="header-item d-flex" id="plg_system_backuponupdate">
	<div class="header-item-content">
		<a class="d-flex" href="javascript:akeeba_backup_on_update_toggle()"
		   title="<?= Text::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_POPOVER_CONTENT_' . ($params['active'] ? 'ACTIVE' : 'INACTIVE')) ?>">

			<div class="d-flex align-items-end mx-auto">
					<span class="fa fa-akbou <?= $params['active'] ? 'fa-akbou-active' : 'fa-akbou-inactive' ?>"
						  aria-hidden="true"></span>
			</div>
			<div class="align-items-center tiny">
				<?= Text::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_' . ($params['active'] ? 'ACTIVE' : 'INACTIVE')) ?>
			</div>
		</a>
	</div>
</div>
