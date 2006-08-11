<div>
	<strong><?php echo JText::_( 'Read more...' ); ?></strong>
</div>
<ul>
<?php for ($j = 0; $j < $links; $j ++) : ?>
	 <?php if ($i >= $total) : break; endif;
			
			$Itemid	= JContentHelper::getItemid($rows[$i]->id);
			$link	= sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$rows[$i]->id.'&amp;Itemid='.$Itemid)
	?>
	<li>
		<a class="blogsection" href="<?php echo $link; ?>">
			<?php echo $rows[$i]->title; ?>
		</a>
	</li>
	<?php
	$i ++;
	endfor; ?>
</ul>