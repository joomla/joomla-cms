<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
$class = $item->params->get('menu-anchor_css', '') ? 'class="'.$item->params->get('menu-anchor_css', '').'" ' : '';
switch ($item->browserNav) :
	default:
	case 0:
?><a <?php echo $class; ?>href="<?php echo $item->flink; ?>"><?php echo $item->title; ?></a><?php
		break;
	case 1:
		// _blank
?><a <?php echo $class; ?>href="<?php echo $item->flink; ?>" target="_blank"><?php echo $item->title; ?></a><?php
		break;
	case 2:
		// window.open
?><a <?php echo $class; ?>href="<?php echo $item->flink.'&amp;tmpl=component'; ?>" onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;"><?php echo $item->title; ?></a>
<?php
		break;
endswitch;
