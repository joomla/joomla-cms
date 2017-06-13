<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$templateparams = $app->getTemplate(true)->params;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::_('behavior.caption');

$cparams = JComponentHelper::getParams('com_media');

// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space
?>
<section class="blog<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading') != 0) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<?php if ($this->params->get('show_category_title')) : ?>
<h2 class="subheading-category">
	<?php echo JHtml::_('content.prepare', $this->category->title, '', 'com_content.category.title'); ?>
</h2>
<?php endif; ?>

<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
	<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
		<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
	<?php endif; ?>
	<?php if ($this->category->description && $this->params->get('show_description')) : ?>
		<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
	<?php endif; ?>
	<div class="clr"></div>
	</div>
<?php endif; ?>

<?php if (empty($this->lead_items) && empty($this->link_items) && empty($this->intro_items)) : ?>
	<?php if ($this->params->get('show_no_articles', 1)) : ?>
		<p><?php echo JText::_('COM_CONTENT_NO_ARTICLES'); ?></p>
	<?php endif; ?>
<?php endif; ?>

<?php $leadingcount = 0; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class="items-leading">
	<?php foreach ($this->lead_items as &$item) : ?>
		<article class="leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? 'system-unpublished' : null; ?>">
			<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
		</article>
		<?php $leadingcount++; ?>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!empty($this->intro_items)) : ?>
	<?php $introcount = count($this->intro_items); ?>
	<?php $counter = 0; ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>
		<?php $rowcount = ((int) $key % (int) $this->columns) + 1; ?>
		<?php if ($rowcount === 1) : ?>
			<?php $row = $counter / $this->columns; ?>
			<div class="items-row cols-<?php echo (int) $this->columns;?> <?php echo 'row-'.$row; ?>">
		<?php endif; ?>
		<article class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
		<?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
		</article>
		<?php $counter++; ?>
		<?php if ($rowcount === (int) $this->columns or $counter === $introcount) : ?>
			<span class="row-separator"></span>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>

<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>
	<?php echo $this->loadTemplate('links'); ?>
<?php endif; ?>

<?php if ($this->params->get('maxLevel') != 0 && is_array($this->children[$this->category->id]) && count($this->children[$this->category->id]) > 0) : ?>
	<div class="cat-children">

	<?php if ($this->params->get('show_category_heading_title_text', 1) == 1) : ?>
		<h3>
			<?php echo JText::_('JGLOBAL_SUBCATEGORIES'); ?>
		</h3>
	<?php endif; ?>
	<?php echo $this->loadTemplate('children'); ?>
	</div>
<?php endif; ?>

<?php if ($this->pagination->pagesTotal > 1 && ($this->params->def('show_pagination', 1) == 1 || $this->params->get('show_pagination') == 2)) : ?>
	<div class="pagination">
	<?php if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
		</p>
	<?php endif; ?>
	<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
<?php endif; ?>

</section>
