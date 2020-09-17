<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();
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
 * administrator/templates/YOUR_TEMPLATE/html/plg_system_backuponupdate/default.css.php
 *
 * where YOUR_TEMPLATE is the folder of the administrator template you are using. Modify that copy. It will be loaded
 * instead of the file in plugins/system/backuponupdate.
 */
?>
.icon-akeeba-backup-on-update {
	background-image: url("../media/com_akeeba/icons/akeeba-16-white.png");
	width: 16px;
	height: 16px;
}

#akeebaBackupOnUpdateStatusContainer a span.badge {
	float: left;
}

#akeebaBackupOnUpdateStatusContainer a span.icon-akeeba-backup-on-update {
	margin: 0 -4px -3px;
}
