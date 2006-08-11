<?php if ( $params->get( 'page_title' ) ) : ?>
	<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo $category->name; ?>
	</div>
<?php endif; ?>

<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
<?php if ( @$category->imgTag || @$category->description ) : ?>
<tr>
	<td valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php 
		if ( isset($category->imgTag) ) :  echo $category->imgTag; endif;
		echo $category->description;
	?>
	</td>
</tr>
<?php endif; ?>
<tr>
	<td width="60%" colspan="2">
	<?php if ( count( $rows ) ) :
		WeblinksViewCategory::showItems( $params, $rows, $catid, $lists, $page  );
	endif; ?>
	</td>
</tr>
</table>