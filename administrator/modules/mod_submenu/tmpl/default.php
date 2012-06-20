<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$hide = JRequest::getInt('hidemainmenu');
?>
<ul id="submenu" class="nav nav-list">
	<?php foreach ($list as $item) : ?>
	<?php if (isset ($item[2]) && $item[2] == 1) :
		?><li class="active"><?php
	else :
		?><li><?php
	endif;
	?>
	<?php
	if ($hide) :
		if (isset ($item[2]) && $item[2] == 1) :
			?><a class="nolink"><?php echo $item[0]; ?></span><?php
		endif;
	else :
		if(strlen($item[1])) :
			?><a href="<?php echo JFilterOutput::ampReplace($item[1]); ?>"><?php echo $item[0]; ?></a><?php
		else :
			?><?php echo $item[0]; ?><?php
		endif;
	endif;
	?>
	</li>
	<?php endforeach; ?>
</ul>
