<?php if ( $params->get( 'page_title' ) ) : ?>
	<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo $params->get('header'); ?>
	</div>
<?php endif; ?>

<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
<?php if ( ($params->get('image') != -1) || $params->get('description') ) : ?>
<tr>
	<td valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php
		if ( isset($data->image) ) :  echo $data->image; endif;
		echo $params->get('description_text');
	?>
	</td>
</tr>
<?php endif; ?>
</table>
<ul>
<?php foreach ( $categories as $category ) : ?>
	<li>
		<a href="<?php echo $category->link ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $category->title;?>
		</a>
		<?php if ( $params->get( 'cat_items' ) ) : ?>
		&nbsp;
		<span class="small">
			(<?php echo $category->numlinks;?>)
		</span>
		<?php endif; ?>
		<?php if ( $params->get( 'cat_description' ) && $category->description ) : ?>
		<br />
		<?php echo $category->description; ?>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>