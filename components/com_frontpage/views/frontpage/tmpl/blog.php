<?php if ($header) : ?>
<div class="componentheading<?php echo $params->get('pageclass_sfx') ?>"><?php echo $header ?></div>
<?php endif; ?>
<table class="blog<?php echo $params->get('pageclass_sfx') ?>" cellpadding="0" cellspacing="0">
<?php if ($menu && $menu->componentid && ($descrip || $descrip_image)) : ?>
<tr>
	<td valign="top">
	<?php if ($params->get('descrip_image') && $description->image) : ?>
		<img src="<?php echo $description->link ?>" align="<?php echo $description->image_position ?>" hspace="6" alt="" />
	<?php endif; ?>
	<?php if ($params->get('description') && $description->description) : ?>
		<?php echo $description->description; ?>
	<?php endif; ?>
	<br/><br/>
	</td>
</tr>
<?php endif; ?>
<?php if ($params->get('leading')) : ?>
<tr>
	<td valign="top">
	<?php for ($i = 0; $i < $leading; $i++) : ?>
		<?php if ($i >= $total) : break; endif; ?>
		<div>
		<?php FrontpageView::showItem($rows[$i], $params, $access, true); ?>
		</div>
	<?php endfor; ?>
	</td>
</tr>
<?php else :
	$i = 0;
endif; ?>

<?php if ($intro && ($i < $total)) : ?>
<tr>
	<td valign="top">
		<table width="100%"  cellpadding="0" cellspacing="0">
		<tr>
			<td>
			<?php 
				$divider = '';
				for ($z = 0; $z < $columns; $z ++) :
					if ($z > 0) : $divider = " column_seperator"; endif; ?>
					<td valign="top" "<?php echo $column_width ?>" class="article_column<?php echo $divider ?>">
					<?php for ($y = 0; $y < $intro / $columns; $y ++) :
						if ($i <= $intro && ($i < $total)) :
							FrontpageView::showItem($rows[$i], $params, $access);
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
<?php if ($links && ($i < $total)) : ?>
<tr>
	<td valign="top">
		<div class="blog_more<?php echo $params->get('pageclass_sfx') ?>">
			<?php FrontpageView::showLinks($rows, $links, $total, $i); ?>
		</div>
	</td>
</tr>
<?php endif; ?>		

<?php if ($usePagination) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $pagination->getPagesLinks('index.php?option=com_frontpage&amp;Itemid='.$Itemid); ?>
		<br /><br />
	</td>
</tr>
<?php endif; ?>
<?php if ($showPaginationResults) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $pagination->getPagesCounter(); ?>
	</td>
</tr>
<?php endif; ?>
</table>