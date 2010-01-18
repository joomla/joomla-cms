<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_wrapper
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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
<div class="contentpane<?php echo $this->params->get('pageclass_sfx'); ?>">
<?php if ($this->params->get('show_page_title', 1)) : ?>
	<h2>
		<?php if ($this->escape($this->params->get('page_heading'))) :?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>
	</h2>
<?php endif; ?>
<iframe <?php echo $this->wrapper->load; ?>
	id="blockrandom"
	name="iframe"
	src="<?php echo $this->wrapper->url; ?>"
	width="<?php echo $this->params->get('width'); ?>"
	height="<?php echo $this->params->get('height'); ?>"
	scrolling="<?php echo $this->params->get('scrolling'); ?>"
	align="top"
	frameborder="0"
	class="wrapper<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo JText::_('NO_IFRAMES'); ?>
</iframe>
</div>
