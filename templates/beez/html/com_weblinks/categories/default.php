<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
$cparams = JComponentHelper::getParams ('com_media');
?>

<?php if ($this->params->get('show_page_title')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>


<div class="weblinks<?php echo $this->params->get('pageclass_sfx'); ?>">

	<?php if ($this->params->def('show_comp_description', 1) || $this->params->def('image', -1) != -1) : ?>
	<div class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">

		<?php if ($this->params->def('image', -1) != -1) : ?>
		<img src="<?php echo $this->baseurl . $cparams->get('image_path').'/'.$this->params->get('image'); ?>" alt="" class="image_<?php echo $this->params->get('image_align'); ?>" />
		<?php endif; ?>

		<?php if ($this->params->get('show_comp_description')) :
			echo $this->params->get('comp_description');
		endif; ?>

		<?php if ($this->params->def('image', -1) != -1) : ?>
		<div class="wrap_image">&nbsp;</div>
		<?php endif; ?>

	</div>
	<?php endif; ?>

</div>


<?php if (count($this->categories)) : ?>
<ul>
	<?php foreach ($this->categories as $category) : ?>
	<li>
		<a href="<?php echo $category->link; ?>" class="category<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo $category->title; ?></a>
		&nbsp;<span class="small">(<?php echo $category->numlinks ?>)</span>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif;
