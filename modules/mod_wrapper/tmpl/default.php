<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_wrapper
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<script type="text/javascript">
	function iFrameHeight() {
		var h = 0;
		if (!document.all) {
			h = document.getElementById('blockrandom').contentDocument.height;
			document.getElementById('blockrandom').style.height = h + 60 + 'px';
		} else if (document.all) {
			h = document.frames('blockrandom').document.body.scrollHeight;
			document.all.blockrandom.style.height = h + 20 + 'px';
		}
	}
</script>

<iframe <?php echo $load; ?>
	id="blockrandom"
	name="<?php echo $target ?>"
	src="<?php echo $url; ?>"
	width="<?php echo $width ?>"
	height="<?php echo $height ?>"
	scrolling="<?php echo $scroll ?>"
	class="wrapper<?php echo $moduleclass_sfx ?>" >
	<?php echo JText::_('MOD_WRAPPER_NO_IFRAMES'); ?>
</iframe>
