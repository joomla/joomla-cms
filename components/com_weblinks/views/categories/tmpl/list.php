<?php if ( $params->def( 'page_title', 1 ) ) : ?>
	<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo $params->get('header'); ?>
	</div>
<?php endif; ?>

<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
<?php if ( ($params->def('image', -1) != -1) || $params->def('description', 1) ) : ?>
<tr>
	<td valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php
		if ( isset($image) ) :  echo $image; endif;
		echo $params->get('description_text');
	?>
	</td>
</tr>
<?php endif; ?>
</table>
<ul>
<?php foreach ( $categories as $category ) : ?>
	<li>
		<a href="<?php echo $category->link; ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $category->name;?>
		</a>
		&nbsp;
		<span class="small">
			(<?php echo $category->numlinks;?>)
		</span>
	</li>
<?php endforeach; ?>
</ul>