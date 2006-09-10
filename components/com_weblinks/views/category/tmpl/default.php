<?php if ( $params->def( 'page_title', 1 ) ) : ?>
	<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo $category->name; ?>
	</div>
<?php endif; ?>

<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
<?php if ( @$category->image || @$category->description ) : ?>
<tr>
	<td valign="top" class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php
		if ( isset($category->image) ) :  echo $category->image; endif;
		echo $category->description;
	?>
	</td>
</tr>
<?php endif; ?>
<tr>
	<td width="60%" colspan="2">
	<?php echo $this->loadTemplate('items'); ?>
	</td>
</tr>
</table>