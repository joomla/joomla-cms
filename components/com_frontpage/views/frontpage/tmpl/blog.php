<?php if (isset($this->data->header)) : ?>
<div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>"><?php echo $this->data->header ?></div>
<?php endif; ?>
<table class="blog<?php echo $this->params->get('pageclass_sfx') ?>" cellpadding="0" cellspacing="0">
<?php if (isset($this->data->description)) : ?>
<tr>
	<td valign="top">
	<?php if ($this->params->get('descrip_image') && $this->data->description->image) : ?>
		<img src="<?php echo $this->data->description->link ?>" align="<?php echo $this->data->description->image_position ?>" hspace="6" alt="" />
	<?php endif; ?>
	<?php if ($this->params->get('description') && $this->data->description->text) : ?>
		<?php echo $this->data->description->text; ?>
	<?php endif; ?>
	<br/><br/>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->get('leading')) : ?>
<tr>
	<td valign="top">
	<?php for ($i = 0; $i < $this->params->get('leading'); $i++) : ?>
		<?php if ($i >= $this->data->total) : break; endif; ?>
		<div>
		<?php $this->item($i); ?>
		</div>
	<?php endfor; ?>
	</td>
</tr>
<?php else : $i = 0; endif; ?>

<?php if ($this->params->get('intro') && ($i < $this->data->total)) : ?>
<tr>
	<td valign="top">
		<table width="100%"  cellpadding="0" cellspacing="0">
		<tr>
			<td>
			<?php
				$divider = '';
				for ($z = 0; $z < $this->params->get('columns'); $z ++) :
					if ($z > 0) : $divider = " column_seperator"; endif; ?>
					<td valign="top" "<?php echo $this->params->get('column_width') ?>" class="article_column<?php echo $divider ?>">
					<?php for ($y = 0; $y < $this->params->get('intro') / $this->params->get('columns'); $y ++) :
						if ($i <= $this->params->get('intro') && ($i < $this->data->total)) :
							$this->item($i);
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
<?php if ($this->params->get('link') && ($i < $this->data->total)) : ?>
<tr>
	<td valign="top">
		<div class="blog_more<?php echo $this->params->get('pageclass_sfx') ?>">
			<?php $this->links($i); ?>
		</div>
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('pagination')) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $this->pagination->getPagesLinks('index.php?option=com_frontpage&amp;Itemid='.$Itemid); ?>
		<br /><br />
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->get('pagination_results')) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
<?php endif; ?>
</table>