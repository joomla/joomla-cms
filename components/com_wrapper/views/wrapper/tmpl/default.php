<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if ($this->params->get('height_auto', 0))
{
	JHtml::_('script', 'com_wrapper/iframe-height.min.js', array('version' => 'auto', 'relative' => true));
}

$isHtml5     = $this->params->get('mode', 'legacy') === 'html5';
$width       = $this->escape($this->params->get('width', '100%'));
$height      = $this->escape($this->params->get('height', '500')) . ($isHtml5 ? 'px' : '');
$scrolling   = $this->escape($this->params->get('scrolling', 'auto'));
$frameborder = $this->escape($this->params->get('frameborder', 1));

// Most current browsers don't support CSS rule "overflow" for scrollbars display
// of IFRAME tags but support HTML4 attribute "scrolling" in HTML5 context.
$scrollingFallback = $scrolling;

if ($isHtml5)
{
	if ($scrolling !== 'auto')
	{
		$scrolling = $scrolling === 'no' ? 'hidden' : 'scroll';
	}

	$frameborder = !$frameborder ? 'none' : $frameborder . 'px solid #000';
}
?>
<div class="contentpane<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1>
				<?php if ($this->escape($this->params->get('page_heading'))) : ?>
					<?php echo $this->escape($this->params->get('page_heading')); ?>
				<?php else : ?>
					<?php echo $this->escape($this->params->get('page_title')); ?>
				<?php endif; ?>
			</h1>
		</div>
	<?php endif; ?>
	<iframe <?php echo $this->wrapper->load; ?>
		id="blockrandom"
		name="iframe"
		src="<?php echo $this->escape($this->wrapper->url); ?>"
		scrolling="<?php echo $scrollingFallback; ?>"
		<?php if ($isHtml5) : ?>
			style="width: <?php echo $width; ?>;
			height: <?php echo $height; ?>;
			overflow: <?php echo $scrolling; ?>;
			border: <?php echo $frameborder; ?>"
		<?php else : ?>
			width="<?php echo $width; ?>"
			height="<?php echo $height; ?>"
			frameborder="<?php echo $frameborder; ?>"
		<?php endif; ?>
		<?php if ($this->escape($this->params->get('page_heading'))) : ?>
			title="<?php echo $this->escape($this->params->get('page_heading')); ?>"
		<?php else : ?>
			title="<?php echo $this->escape($this->params->get('page_title')); ?>"
		<?php endif; ?>
		class="wrapper<?php echo $this->pageclass_sfx; ?>">
		<?php echo JText::_('COM_WRAPPER_NO_IFRAMES'); ?>
	</iframe>
</div>
