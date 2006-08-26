<ul id="archive-list" style="list-style: none;">
<?php foreach ($this->items as $item) : ?>
<li class="row<?php echo ($item->odd +1 ); ?>">
	<?php if ($this->params->get('title'))              : ?>    
	<h4 class="title">
		<a href="<?php echo JURI::resolve('index.php?option=com_content&amp;view=article&amp;id='.$item->id.'&amp;Itemid='.$Itemid); ?>">
			<?php echo $item->title; ?>
		</a>
	</h4>
	<?php endif; ?>
	<h5 class="metadata">
		<?php if ($this->params->get('date')) : ?>
		<span class="created-date">
			<?php echo JText::_('Created').': '.$item->created; ?>
		</span>
		<?php endif; ?>
		<?php if ($this->params->get('author')) : ?>
		<span class="author">
			<?php echo JText::_('Author').': '; echo $item->created_by_alias ? $item->created_by_alias : $item->author; ?>
		</span>
		<?php endif; ?>
		<?php if ($this->params->get('hits')) : ?>
		<span class="hits">
			<?php echo JText::_('Hits').': '; echo $item->hits ? $item->hits : '-'; ?>
		</span>
		<?php endif; ?>
	</h5>
	<div class="intro">
		<?php echo substr($item->introtext, 0, 255); ?>...
	</div>
</li>
<?php endforeach; ?>
</ul>
<?php if ($this->params->get('navigation')) : ?>
<div id="navigation">
	<span><?php echo $this->pagination->writePagesLinks($this->data->link); ?></span>
	<span><?php echo $this->pagination->writePagesCounter(); ?></span>
</div>
<?php endif; ?>
