<?php
/**
 * @version		$Id: default_button.php 19472 2010-11-14 20:18:44Z dextercowley $
 * @package		Joomla.Administrator
 * @subpackage	mod_quickicon
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$btnArr = explode(".png",$button['image']);
?>
<li>
	<a href="<?php echo $button['link']; ?>" class="<?php echo $btnArr[0];?>">			
		<span><?php echo $button['text']; ?></span>
	</a>
</li>
	