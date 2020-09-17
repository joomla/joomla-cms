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
 * administrator/templates/YOUR_TEMPLATE/html/plg_system_backuponupdate/joomla4.css.php
 *
 * where YOUR_TEMPLATE is the folder of the administrator template you are using. Modify that copy. It will be loaded
 * instead of the file in plugins/system/backuponupdate.
 */
?>

@font-face
{
	font-family: "Akeeba Products for Joomla Status";
	font-style: normal;
	font-weight: normal;
	src: url("../media/com_akeeba/fonts/akeeba/Akeeba-Products.woff") format("woff");
}

span.fa-akbou:before
{
	display: inline-block;
	font-family: 'Akeeba Products for Joomla Status';
	font-style: normal;
	font-weight: normal;
	line-height: 1;
	-webkit-font-smoothing: antialiased;
	position: relative;
	-moz-osx-font-smoothing: grayscale;
	color: var(--primary-dark);
	background: transparent;
}

span[class*=fa-akbou]:before
{
	content: 'B';
}

span.fa-akbou-active:before
{
	color: var(--primary-dark);
}

span.fa-akbou-inactive:before
{
	color: var(--warning);
}
