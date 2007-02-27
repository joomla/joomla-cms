<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id$
 * @author Design & Accessible Team ( Angie Radtke / Robert Deutz )
 * @package Joomla
 * @subpackage Accessible-Template-Beez
 * @copyright Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

if (count($list)) {
	if (count($list) == 1) {
		$item = $list[0];
		modNewsFlashHelper :: renderItem($item, $params, $access);
	} else {
		echo '<ul class="horiz' . $params->get('moduleclass_sfx') . '">';
		foreach ($list as $item) {
			echo '<li>';
			modNewsFlashHelper :: renderItem($item, $params, $access);
			echo '</li>';
		}
	}
}
?>