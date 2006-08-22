<?php if ( $this->params->get( 'page_title' ) ) : ?>
	<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo $this->params->get('header'); ?>
	</div>
<?php endif; ?>

<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<?php if ( ($this->params->get('image') != -1) || $this->params->get('description') ) : ?>
<tr>
	<td valign="top" class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php 
		if ( isset($this->data->image) ) :  echo $this->data->image; endif;
		echo $this->params->get('description_text');
	?>
	</td>
</tr>
<?php endif; ?>
</table>
<ul>
<?php foreach ( $this->data->categories as $category ) : 
	$link = 'index.php?option=com_weblinks&amp;task=category&amp;catid='. $category->catid .'&amp;Itemid='. $Itemid;
	?>
	<li>
		<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php echo $category->name;?>
		</a>
		&nbsp;
		<span class="small">
			(<?php echo $category->numlinks;?>)
		</span>
	</li>
<?php endforeach; ?>
</ul>