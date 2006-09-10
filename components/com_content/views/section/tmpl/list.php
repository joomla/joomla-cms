<?php if ($params->get('page_title')) : ?>
<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php echo $section->name; ?>
</div>
<?php endif; ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td width="60%" valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
	<?php if ($section->image) : ?>
		<img src="images/stories/<?php echo $section->image;?>" align="<?php echo $section->image_position;?>" hspace="6" alt="<?php echo $section->image;?>" />
	<?php endif; ?>
	<?php echo $section->description; ?>
	</td>
</tr>
<tr>
	<td colspan="2">
	<?php if ($params->def('other_cat_section', 1)) : ?>
	<ul>
	<?php foreach ($categories as $category) : ?>
		<li>
			<a href="<?php echo $category->link; ?>" class="category">
				<?php echo $category->name;?>
			</a>
			<?php if ($params->get('cat_items')) : ?>
			&nbsp;
			<span class="small">
				( <?php echo $category->numitems ." ". JText::_( 'items' );?> )
			</span>
			<?php endif; ?>
			<?php if ($params->def('cat_description', 1) && $category->description) : ?>
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
