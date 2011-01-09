<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; 
$cparams = JComponentHelper::getParams('com_media');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
?>
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</div>
<?php endif; ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<tr>
	<td width="60%" valign="top" class="contentdescription<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" colspan="2">
	<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
		<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
			<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
		<?php endif; ?>
		<?php if ($this->params->get('show_description') && $this->category->description) : ?>
			<?php echo JHtml::_('content.prepare', $this->category->description); ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>	
</td>
</tr>
<tr>
	<td>
		<?php echo $this->loadTemplate('articles'); ?>


	</td>
</tr>
</table>
<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
	<div class="cat-children">
		<h3>
			<?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?>
		</h3>

		<?php echo $this->loadTemplate('children'); ?>

	</div>
<?php endif; ?>
