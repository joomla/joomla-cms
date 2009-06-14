<?php // no direct access
defined('_JEXEC') or die; ?>
<?php if ($this->params->get('show_page_title', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
<?php endif; ?>

<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->params->get('pageclass_sfx'); ?>">
<?php if (@$this->category->image || @$this->category->description) : ?>
<tr>
	<td valign="top" class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php
		if (isset($this->category->image)) :  echo $this->category->image; endif;
		echo $this->category->description;
	?>
	</td>
</tr>
<?php endif; ?>
<?php if (count($this->children)) : ?>
<tr>
	<td>
	<ul>
<?php foreach ($this->children as $category) : ?>
	<li>
		<a href="<?php echo $category->link ?>" class="category<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo $category->title;?></a>
		<?php if ($this->params->get('show_cat_items')) : ?>
		&nbsp;
		<span class="small">
			(<?php echo $category->numitems;?>)
		</span>
		<?php endif; ?>
		<?php if ($this->params->get('show_cat_description') && $category->description) : ?>
		<br />
		<?php echo $category->description; ?>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>
</td>
</tr>
<?php endif; ?>
<?php if (count($this->items)) : ?>
<tr>
	<td width="60%" colspan="2">
	<?php echo $this->loadTemplate('items'); ?>
	</td>
</tr>
<?php endif; ?>
</table>