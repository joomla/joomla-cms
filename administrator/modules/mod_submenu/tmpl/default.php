<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	mod_submenu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$hide = JRequest::getInt('hidemainmenu');
?>
<ul id="submenu">
	<?php foreach ($list as $item) : ?>
	<li>
	<?php
	if ($hide) :
		if (isset ($item[2]) && $item[2] == 1) :
			?><span class="nolink active"><?php echo $item[0]; ?></span><?php
		else :
			?><span class="nolink"><?php echo $item[0]; ?></span><?php
		endif;
	else :
		if(strlen($item[1])) :
			if (isset ($item[2]) && $item[2] == 1) :
				?><a class="active" href="<?php echo JFilterOutput::ampReplace($item[1]); ?>"><?php echo $item[0]; ?></a><?php
			else :
				?><a href="<?php echo JFilterOutput::ampReplace($item[1]); ?>"><?php echo $item[0]; ?></a><?php
			endif;
		else :
			?><?php echo $item[0]; ?><?php
		endif;
	endif;
	?>
	</li>
	<?php endforeach; ?>
</ul>
