<?php
/**
 * $Id$
 */
defined('_JEXEC') or die;

$cparams = &JComponentHelper::getParams('com_media');
?>

<div class="jcontact-category<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_title', 1)) : ?>
		<h2>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		</h2>
	<?php endif; ?>

	<?php if (!empty($this->category->image) || $this->category->description) : ?>
		<div class="jdescription">
			<?php if ($this->params->get('image') != -1 && $this->params->get('image') != '') : ?>
				<img src="<?php echo $this->baseurl .'/'. 'images/stories' . '/'. $this->params->get('image'); ?>" class="jalign<?php echo $this->params->get('image_align'); ?>" alt="<?php echo JText::_('Contacts'); ?>" />
			<?php elseif (!empty($this->category->image)) : ?>
				<img src="<?php echo $this->baseurl .'/'. 'images/stories' . '/'. $this->category->image; ?>" class="jalign<?php echo $this->category->image_position; ?>" alt="<?php echo JText::_('Contacts'); ?>" />
			<?php endif; ?>

			<?php echo $this->category->description; ?>
		</div>
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

