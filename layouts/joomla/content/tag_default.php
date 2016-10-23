<?php
/**
 * @package Tag View Feature for Joomla!
 * @copyright Copyright (c) 2016 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('JPATH_BASE') or die;

/**
 * Note that this layout opens a div with the page class suffix. If you do not use the category children
 * layout you need to close this div either by overriding this file or in your main layout.
 */
$params    = $displayData->params;
$tag = $displayData->tag;
?>
<div>
	<div class="<?php echo $className .'-category' . $displayData->pageclass_sfx;?>">
		<?php if ($params->get('show_page_heading')) : ?>
			<h1>
				<?php echo $displayData->escape($params->get('page_heading')); ?>
			</h1>
		<?php endif; ?>

		<?php if ($params->get('show_tag_title', 1) or $params->get('page_subheading')) : ?>
			<h2> <?php echo $this->escape($params->get('page_subheading')); ?>
				<?php if ($params->get('show_tag_title')) : ?>
					<span class="subheading-category"><?php echo $tag->title; ?></span>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<?php if ($params->get('show_tag_description', 1) || $params->def('show_tag_image', 1)) : ?>
			<div class="category-desc clearfix">
				<?php if ($params->get('show_tag_image') && $tag->images->get('image_intro')) : ?>
					<img src="<?php echo $tag->images->get('image_intro'); ?>" alt="<?php echo htmlspecialchars($tag->images->get('image_intro_alt'), ENT_COMPAT, 'UTF-8'); ?>"/>
				<?php endif; ?>
				<?php if ($params->get('show_tag_description') && $tag->description) : ?>
					<?php echo JHtml::_('content.prepare', $tag->description, '', 'com_content.tag'); ?>
				<?php endif; ?>
			    <div class="clr"></div>
			</div>
		<?php endif; ?>

		<?php echo $displayData->loadTemplate($displayData->subtemplatename); ?>

		<?php if ($displayData->get('children') && $displayData->tagMaxLevel != 0) : ?>
			<div class="cat-children">
				<?php if ($params->get('show_category_heading_title_text', 1) == 1) : ?>
					<h3>
						<?php echo JText::_('JGLOBAL_SUBCATEGORIES'); ?>
					</h3>
				<?php endif; ?>
				<?php echo $displayData->loadTemplate('children'); ?>
			</div>
		<?php endif; ?>
	</div>
</div>

