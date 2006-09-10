<?php if ($params->get('page_title')) : ?>
<div class="componentheading<?php echo $params->get('pageclass_sfx') ?>">
	<?php echo $params->get('header'); ?>
</div>
<?php endif; ?>
<table class="blog<?php echo $params->get('pageclass_sfx') ?>" cellpadding="0" cellspacing="0">
<?php if (isset($frontpage->description)) : ?>
<tr>
	<td valign="top">
	<?php if ($params->get('descrip_image') && $frontpage->description->image) : ?>
		<img src="<?php echo $this->frontpage->description->link ?>" align="<?php echo $frontpage->description->image_position ?>" hspace="6" alt="" />
	<?php endif; ?>
	<?php if ($params->get('description') && $frontpage->description->text) : ?>
		<?php echo $frontpage->description->text; ?>
	<?php endif; ?>
	<br/><br/>
	</td>
</tr>
<?php endif; ?>
<?php if ($params->def('leading', 1)) : ?>
<tr>
	<td valign="top">
	<?php for ($i = 0; $i < $params->get('leading'); $i++) : ?>
		<?php if ($i >= $frontpage->total) : break; endif; ?>
		<div>
		<?php 
			$this->item =& $this->getItem($i, $params);
			echo $this->loadTemplate('item'); 
		?>
		</div>
	<?php endfor; ?>
	</td>
</tr>
<?php else : $i = 0; endif; ?>

<?php if ($params->def('intro', 4) && ($i < $frontpage->total)) : ?>
<tr>
	<td valign="top">
		<table width="100%"  cellpadding="0" cellspacing="0">
		<tr>
		<?php
			$divider = '';
			for ($z = 0; $z < $params->def('columns', 2); $z ++) :
				if ($z > 0) : $divider = " column_seperator"; endif; ?>
				<td valign="top" width="<?php echo intval(100 / $params->get('columns')) ?>%" class="article_column<?php echo $divider ?>">
				<?php for ($y = 0; $y < $params->get('intro') / $params->get('columns'); $y ++) :
					if ($i <= $params->get('intro') && ($i < $frontpage->total)) :
						$this->item =& $this->getItem($i, $params);
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
<?php if ($params->def('link', 4) && ($i < $frontpage->total)) : ?>
<tr>
	<td valign="top">
		<div class="blog_more<?php echo $params->get('pageclass_sfx') ?>">
			<?php 
				$this->links = array_splice($items, $i);
				echo $this->loadTemplate('links'); 
			?>
		</div>
	</td>
</tr>
<?php endif; ?>

<?php if ($params->def('pagination', 2)) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $pagination->getPagesLinks('index.php?option=com_frontpage&amp;Itemid='.$Itemid); ?>
		<br /><br />
	</td>
</tr>
<?php endif; ?>
<?php if ($params->def('pagination_results', 1)) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $pagination->getPagesCounter(); ?>
	</td>
</tr>
<?php endif; ?>
</table>