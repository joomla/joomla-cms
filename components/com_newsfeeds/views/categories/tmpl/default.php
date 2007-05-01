<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ( $this->params->get( 'show_page_title' ) ) : ?>
	<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo $this->params->get('page_title'); ?>
	</div>
<?php endif; ?>

<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<?php if ( ($this->params->get('image') != -1) || $this->params->get('show_comp_description') ) : ?>
<tr>
	<td valign="top" class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php
		if ( isset($this->image) ) :  echo $this->image; endif;
		echo $this->params->get('comp_description');
	?>
	</td>
</tr>
<?php endif; ?>
</table>
<ul>
<?php foreach ( $this->categories as $category ) : ?>
	<li>
		<a href="<?php echo $category->link ?>" class="category<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php echo $category->title;?>
		</a>
		<?php if ( $this->params->get( 'show_cat_items' ) ) : ?>
		&nbsp;
		<span class="small">
			(<?php echo $category->numlinks;?>)
		</span>
		<?php endif; ?>
		<?php if ( $this->params->get( 'show_cat_description' ) && $category->description ) : ?>
		<br />
		<?php echo $category->description; ?>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>