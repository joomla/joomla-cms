<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

switch ($item->browserNav) :
	default:
	case 0:
?>
	<a href="<?php echo $item->link; ?>" class="">
		<?php echo $item->title; ?></a>
<?php
		break;
	case 1:
		// _blank
?>
	<a href="<?php echo $item->link; ?>" class="" target="_blank">
		<?php echo $item->title; ?></a>
<?php
		break;
	case 2:
		// window.open
		$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$this->_params->get('window_open');
?>
	<a href="<?php echo $item->link.'&tmpl=component'; ?>" class="" onclick="window.open(this.href,'targetWindow','<?php echo $attribs;?>');return false;">
		<?php echo $item->title; ?></a>
<?php
		break;
endswitch;

