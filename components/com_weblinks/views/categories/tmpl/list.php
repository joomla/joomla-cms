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
		if ( isset($image) ) :  echo $image; endif;
		echo $params->get('description_text');
	?>
	</td>
</tr>
<?php endif; ?>
</table>
<ul>
<?php foreach ( $categories as $cat ) : 
	$link = 'index.php?option=com_weblinks&amp;task=category&amp;catid='. $cat->catid .'&amp;Itemid='. $Itemid;
	?>
	<li>
		<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $cat->name;?>
		</a>
		&nbsp;
		<span class="small">
			(<?php echo $cat->numlinks;?>)
		</span>
	</li>
<?php endforeach; ?>
</ul>