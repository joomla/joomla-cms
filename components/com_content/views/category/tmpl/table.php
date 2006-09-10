<?php if ($params->get('page_title')) : ?>
	<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo $category->name; ?>
	</div>
<?php endif; ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td width="60%" valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
	<?php if ($category->image) : ?>
		<img src="images/stories/<?php echo $category->image;?>" align="<?php echo $category->image_position;?>" hspace="6" alt="<?php echo $this->category->image;?>" />
	<?php endif; ?>
	<?php echo $category->description; ?>
</td>
</tr>
<tr>
	<td>
	<?php 
		$this->items =& $this->getItems(); 
		echo $this->loadTemplate('items');
	?>
	
	<?php if ($access->canEdit || $access->canEditOwn) :
		echo $this->getIcon($this->items[0], 'new');
	endif; ?>
	</td>
</tr>
</table>