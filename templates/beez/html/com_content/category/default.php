<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php if ($this->params->get('show_page_title')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->category->title; ?>
</h1>
<?php endif; ?>

<?php if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
<div class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_description_image') && $this->category->image) : ?>
	<img src="images/stories/<?php echo $this->category->image; ?>" class="image_<?php echo $this->category->image_position; ?>" />
	<?php endif; ?>

	<?php if ($this->params->get('show_description') && $this->category->description) :
		echo $this->category->description;
	endif; ?>

	<?php if ($this->params->get('show_description_image') && $this->category->image) : ?>
	<div class="wrap_image">&nbsp;</div>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php $this->items =& $this->getItems();
echo $this->loadTemplate('items'); ?>

<?php if ($this->access->canEdit || $this->access->canEditOwn) :
	echo JHTML::_('icon.create', $this->category, $this->params, $this->access);
endif;
