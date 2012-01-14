<?php
/**
 * @version		$Id: default.php 14276 2010-01-18 14:20:28Z louis $
 * @package		Joomla.Administrator
 * @subpackage	mod_quickicon
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$buttons = QuickIconHelper::getButtons();
?>
<div id="cpanel">
<ul>
<?php
foreach ($buttons as $button):
	echo QuickIconHelper::button($button);
endforeach;
?>
</ul>
</div>
