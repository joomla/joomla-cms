<?php
/**
 * @version		$Id: mod_banners.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	mod_wrapper
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;
?>
<script language="javascript" type="text/javascript">
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
	align="top"
	frameborder="0"
	class="wrapper<?php echo $class ?>">
	<?php echo JText::_('NO_IFRAMES'); ?>
</iframe>