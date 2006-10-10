<?php if ($this->params->get('page_title')) : ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo $this->section->name; ?>
</div>
<?php endif; ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td width="60%" valign="top" class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>" colspan="2">
	<?php if ($this->params->get('description_image') && $this->section->image) : ?>
		<img src="images/stories/<?php echo $this->section->image;?>" align="<?php echo $this->section->image_position;?>" hspace="6" alt="<?php echo $this->section->image;?>" />
	<?php endif; ?>
	<?php if ($this->params->get('description') && $this->section->description) : ?>
		<?php echo $this->section->description; ?>
	<?php endif; ?>
	</td>
</tr>
<tr>
	<td colspan="2">
	<?php if ($this->params->def('other_cat_section', 1)) : ?>
	<ul>
	<?php foreach ($this->categories as $category) : ?>
		<li>
			<a href="<?php echo $category->link; ?>" class="category">
				<?php echo $category->name;?>
			</a>
			<?php if ($this->params->get('cat_items')) : ?>
			&nbsp;
			<span class="small">
				( <?php echo $category->numitems ." ". JText::_( 'items' );?> )
			</span>
			<?php endif; ?>
			<?php if ($this->params->def('cat_description', 1) && $category->description) : ?>
			<br />
			<?php echo $category->description; ?>
			<?php else : echo $category->name; ?>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	</td>
</tr>
</table>
