<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->params->get('show_page_title')) : ?>
	<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo $this->category->title; ?>
	</div>
<?php endif; ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td width="60%" valign="top" class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>" colspan="2">
	<?php if ($this->category->image) : ?>
		<img src="images/stories/<?php echo $this->category->image;?>" align="<?php echo $this->category->image_position;?>" hspace="6" alt="<?php echo $this->this->category->image;?>" />
	<?php endif; ?>
	<?php echo $this->category->description; ?>
</td>
</tr>
<tr>
	<td>
	<?php
		$this->items =& $this->getItems();
		echo $this->loadTemplate('items');
	?>

	<?php if ($this->access->canEdit || $this->access->canEditOwn) :
			echo JHTML::_('icon.create', $this->category  , $this->params, $this->access);
	endif; ?>
	</td>
</tr>
</table>