<?php // no direct access
defined('_JEXEC') or die;
$pageClass = $this->params->get('pageclass_sfx');
?>

<div class="jnewsfeed-category<?php echo $pageClass;?>">

<?php if ($this->params->def('show_page_title', 1)) : ?>
	<h2>
		<?php if ($this->escape($this->params->get('page_heading'))) :?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>
	</h2>
<?php endif; ?>

<?php  /**
TODO fix images in com_categories ?>
<?php if ($this->category->image) : ?>
	<?php
		// Define image tag attributes
		$attribs['align']	= $this->category->image_position;
		$attribs['hspace']	= 6;

		// Use the static HTML library to build the image tag
		echo JHtml::_('image', 'images/'.$this->category->image, JText::_('News Feeds'), $attribs);
	?>
<?php endif; ?>
<?php  **/ ?>
<?php if ($this->category->description) : ?>
	<p>
		<?php echo $this->category->description; ?>
	</p>
<?php endif; ?>

<?php echo $this->loadTemplate('items'); ?>

<div class="jcat-siblings">
<?php  echo $this->loadTemplate('siblings');  ?>
</div>

<div class="jcat-children">
<?php echo $this->loadTemplate('children'); ?>
</div>

<div class="jcat-parents">
<?php  echo $this->loadTemplate('parents');  ?>
</div>

</div>

