<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	mod_quickicon
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$buttons = modQuickIconHelper::getButtons();
?>
<div id="cpanel">
<?php
foreach ($buttons as $button):
	echo modQuickIconHelper::button($button);
endforeach;
?>
</div>
