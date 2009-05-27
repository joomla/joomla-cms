<?php // @version $Id$
defined('_JEXEC') or die;
$cparams = JComponentHelper::getParams ('com_media');
?>

<?php if ($this->params->get('show_page_title',1)) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>

<?php if ($this->params->def('show_comp_description', 1) || $this->params->get('image', -1) != -1) : ?>
<div class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">

	<?php if ($this->params->get('image', -1) != -1) : ?>
	<img src="<?php echo $this->baseurl . '/' . $cparams->get('image_path').'/'.$this->params->get('image'); ?>" class="image_<?php echo $this->params->get('image_align'); ?>" />
	<?php endif; ?>

	<?php echo $this->params->get('comp_description'); ?>

	<?php if ($this->params->get('image', -1) != -1) : ?>
	<div class="wrap_image">&nbsp;</div>
	<?php endif; ?>

</div>
<?php endif; ?>

<?php if (count($this->categories)) : ?>
<ul>
	<?php foreach ($this->categories as $category) : ?>
	<li>
		<a href="<?php echo $category->link; ?>" class="category">
			<?php echo $category->title; ?></a>
		<?php if ($this->params->get('show_cat_items')) : ?>
		&nbsp;<span class="small">(<?php echo $category->numlinks . ' ' . JText::_('items'); ?>)</span>
		<?php endif; ?>
		<?php if ($this->params->def('show_cat_description', 1) && $category->description) : ?>
		<br />
		<?php echo $category->description; ?>
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif;
