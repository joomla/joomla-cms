<div>
	<strong><?php echo JText::_( 'Read more...' ); ?></strong>
</div>
<ul>
<?php foreach ($this->links as $link) : ?>
	<li>
		<a class="blogsection" href="<?php echo $link->link; ?>">
			<?php echo $link->title; ?>
		</a>
	</li>
<?php endforeach; ?>
</ul>