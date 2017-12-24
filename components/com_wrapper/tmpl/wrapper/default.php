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

if ($this->escape($this->params->get('page_heading')))
{
	$title = $this->escape($this->params->get('page_heading'));
}
else
{
	$title = $this->escape($this->params->get('page_title'));
}


HTMLHelper::_('webcomponent', ['joomla-iframe' => 'com_wrapper/webcomponents/joomla-iframe.min.js'], ['version' => 'auto', 'relative' => true]);
?>
<div class="contentpane<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1><?php echo $title; ?></h1>
		</div>
	<?php endif; ?>
	<joomla-iframe iframe-auto-height="<?php echo $this->wrapper->load; ?>"
			iframe-name="iframe"
			iframe-src="<?php echo $this->escape($this->wrapper->url); ?>"
			iframe-width="<?php echo $this->escape($this->params->get('width')); ?>"
			iframe-height="<?php echo $this->escape($this->params->get('height')); ?>"
			iframe-scrolling="<?php echo $this->escape($this->params->get('scrolling')); ?>"
			iframe-border="<?php echo $this->escape($this->params->get('frameborder', 1)); ?>"
			iframe-class="wrapper <?php echo $this->pageclass_sfx; ?>"
			iframe-title="<?php echo $title; ?>">
		<noscript>
			<iframe name="iframe"
					title="<?php echo $title; ?>"
					id="iframe-<?php echo rand(1, 10000); ?>"
					src="<?php echo $this->escape($this->wrapper->url); ?>"
					width="<?php echo $this->escape($this->params->get('width')); ?>"
					height="<?php echo $this->escape($this->params->get('height')); ?>"
					scrolling="<?php echo $this->escape($this->params->get('scrolling')); ?>"
					frameborder="<?php echo $this->escape($this->params->get('frameborder', 1)); ?>"
					class="wrapper <?php echo $this->pageclass_sfx; ?>"></iframe>
		</noscript>
	</joomla-iframe>
</div>
