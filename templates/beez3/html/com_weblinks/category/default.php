<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::_('behavior.caption');
?>
<div class="weblink-category<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>
<?php if ($this->params->get('show_category_title', 1)) : ?>
<h2>
	<?php echo JHtml::_('content.prepare', $this->category->title, '', 'com_weblinks.category.title'); ?>
</h2>
<?php endif; ?>
<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
	<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
		<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
	<?php endif; ?>
	<?php if ($this->category->description && $this->params->get('show_description')) : ?>
		<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_weblinks.category'); ?>
	<?php endif; ?>
	<div class="clr"></div>
	</div>
<?php endif; ?>
<?php echo $this->loadTemplate('items'); ?>
<?php if ($this->maxLevel != 0 && !empty($this->children[$this->category->id])) : ?>
	<div class="cat-children">
	<h3><?php echo JText::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
	<?php echo $this->loadTemplate('children'); ?>
	</div>
<?php endif; ?>
</div>
