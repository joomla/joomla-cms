<?php if ($this->params->get('page_title')) : ?>
<div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
	<?php echo $this->params->get('header'); ?>
</div>
<?php endif; ?>
<table class="blog<?php echo $this->params->get('pageclass_sfx') ?>" cellpadding="0" cellspacing="0">
<?php if (isset($this->frontpage->description)) : ?>
<tr>
	<td valign="top">
	<?php if ($this->params->get('descrip_image') && $this->frontpage->description->image) : ?>
		<img src="<?php echo $this->frontpage->description->link ?>" align="<?php echo $this->frontpage->description->image_position ?>" hspace="6" alt="" />
	<?php endif; ?>
	<?php if ($this->params->get('description') && $this->frontpage->description->text) : ?>
		<?php echo $this->frontpage->description->text; ?>
	<?php endif; ?>
	<br/><br/>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->def('leading', 1)) : ?>
<tr>
	<td valign="top">
	<?php for ($i = $this->pagination->limitstart; $i < $this->params->get('leading'); $i++) : ?>
		<?php if ($i >= $this->total) : break; endif; ?>
		<div>
		<?php 
			$this->item =& $this->getItem($i, $this->params);
			echo $this->loadTemplate('item'); 
		?>
		</div>
	<?php endfor; ?>
	</td>
</tr>
<?php else : $i = $this->limitstart; endif; ?>

<?php if ($this->params->def('intro', 4) && ($i < $this->total)) : ?>
<tr>
	<td valign="top">
		<table width="100%"  cellpadding="0" cellspacing="0">
		<tr>
		<?php
			$divider = '';
			for ($z = 0; $z < $this->params->def('columns', 2); $z ++) :
				if ($z > 0) : $divider = " column_seperator"; endif; ?>
				<td valign="top" width="<?php echo intval(100 / $this->params->get('columns')) ?>%" class="article_column<?php echo $divider ?>">
				<?php for ($y = 0; $y < $this->params->get('intro') / $this->params->get('columns'); $y ++) :
					if ($i < $this->total) :
						$this->item =& $this->getItem($i, $this->params);
						echo $this->loadTemplate('item');
						$i ++;
					endif;
				endfor; ?>
				</td>
		<?php endfor; ?>
		</tr>
		</table>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->def('link', 4) && ($i < $this->total)) : ?>
<tr>
	<td valign="top">
		<div class="blog_more<?php echo $this->params->get('pageclass_sfx') ?>">
			<?php 
				$this->links = array_splice($this->items, $i);
				echo $this->loadTemplate('links'); 
			?>
		</div>
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->def('pagination', 2)) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $this->pagination->getPagesLinks(); ?>
		<br /><br />
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->def('pagination_results', 1)) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
<?php endif; ?>
</table>