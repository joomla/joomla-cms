<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');

?>
<div class="tag-category<?php echo $this->pageclass_sfx;?>">
<?php  if ($this->item->get('show_page_heading')) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif;  ?>
<?php if($this->item->get('show_tag_title', 1)) : ?>
<h2>
	<?php echo JHtml::_('content.prepare', $this->item->title, '', 'com_tag.tag'); ?>
</h2>
<?php endif;  ?>
<?php  if ($this->item->get('show_description', 1)) : ?>
	<div class="category-desc"><?php $this->item->get('show_description', 1); ?>
	<?php if ($this->item->get('show_description', 1) && $this->item->description) : ?>
		<?php echo JHtml::_('content.prepare', $this->item->description, '', 'com_tags.tag'); ?>
	<?php endif; ?>
	<div class="clr"></div>
	</div>
<?php endif; ?>

<?php echo $this->loadTemplate('items'); ?>

</div>
