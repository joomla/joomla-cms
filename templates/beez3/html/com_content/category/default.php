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

$pageClass = $this->params->get('pageclass_sfx');
?>
<section class="category-list<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<?php if ($this->params->get('show_page_heading') and ($this->params->get('show_category_title') or $this->params->get('page_subheading'))) : ?>
<hgroup>
<?php endif; ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<?php if ($this->params->get('show_category_title') or $this->params->get('page_subheading')) : ?>
<h2>
	<?php echo $this->escape($this->params->get('page_subheading')); ?>
	<?php if ($this->params->get('show_category_title'))
	{
		echo '<span class="subheading-category">'.JHtml::_('content.prepare', $this->category->title, '', 'com_content.category.title').'</span>';
	}
	?>
</h2>
<?php if ($this->params->get('show_page_heading') and ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading'))) : ?>
</hgroup>
<?php endif; ?>
<?php endif; ?>

<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
	<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
		<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
	<?php endif; ?>
	<?php if ($this->params->get('show_description') && $this->category->description) : ?>
		<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
	<?php endif; ?>
	<div class="clr"></div>
	</div>
<?php endif; ?>


<?php if (is_array($this->children[$this->category->id]) && count($this->children[$this->category->id]) > 0 && $this->params->get('maxLevel') != 0) : ?>
		<div class="cat-children">

	<?php if ($this->params->get('show_category_title') or $this->params->get('page_subheading'))
	{
		echo '<h3>';
	}
	elseif ($this->params->get('show_category_heading_title_text', 1) == 1)
	{
		echo '<h2>';
	} ?>
    <?php if ($this->params->get('show_category_heading_title_text', 1) == 1) : ?>
		<?php echo JText::_('JGLOBAL_SUBCATEGORIES'); ?>
	<?php endif; ?>
	<?php if ($this->params->get('show_category_title') or $this->params->get('page_subheading'))
	{
		echo '</h3>';
	}
	elseif ($this->params->get('show_category_heading_title_text', 1) == 1)
	{
		echo '</h2>';
	} ?>
		</div>
	<?php endif; ?>
	<?php echo $this->loadTemplate('children'); ?>


	<div class="cat-items">
		<?php echo $this->loadTemplate('articles'); ?>
	</div>

</section>

