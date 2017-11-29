<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

HtmlHelper::_('webcomponent', 'com_wrapper/webcomponents/joomla-iframe.min.js', ['version' => 'auto', 'relative' => true]);
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
	<joomla-iframe auto-height="<?php echo $this->wrapper->load; ?>"
			name="iframe"
			src="<?php echo $this->escape($this->wrapper->url); ?>"
			width="<?php echo $this->escape($this->params->get('width')); ?>"
			height="<?php echo $this->escape($this->params->get('height')); ?>"
			scrolling="<?php echo $this->escape($this->params->get('scrolling')); ?>"
			frameborder="<?php echo $this->escape($this->params->get('frameborder', 1)); ?>"
			use-class="wrapper<?php echo $this->pageclass_sfx; ?>"
			no-frame-text="<?php echo Text::_('COM_WRAPPER_NO_IFRAMES'); ?>">
		<noscript><iframe name="<?php echo $target; ?>"
					src="<?php echo $url; ?>"
					width="<?php echo $width; ?>"
					height="<?php echo $height; ?>"
					scrolling="<?php echo $scroll; ?>"
					frameborder="<?php echo $frameborder; ?>"
					class="wrapper"><?php echo Text::_('COM_WRAPPER_NO_IFRAMES'); ?></iframe>
		</noscript>
	</joomla-iframe>
</div>
