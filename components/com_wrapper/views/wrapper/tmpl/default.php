<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('script', 'com_wrapper/iframe-height.min.js', array('version' => 'auto', 'relative' => true));

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
		style="width: <?php echo $this->escape($this->params->get('width')); ?>;
		       height: <?php echo $this->escape($this->params->get('height')); ?>;
		       overflow: <?php echo $this->escape($this->params->get('overflow')); ?>;
		       border: <?php echo $this->escape($this->params->get('border', 1)); ?>;"
		<?php if ($this->escape($this->params->get('page_heading'))) : ?>
			title="<?php echo $this->escape($this->params->get('page_heading')); ?>"
		<?php else : ?>
			title="<?php echo $this->escape($this->params->get('page_title')); ?>"
		<?php endif; ?>
		class="wrapper<?php echo $this->pageclass_sfx; ?>">
		<?php echo JText::_('COM_WRAPPER_NO_IFRAMES'); ?>
	</iframe>
</div>
