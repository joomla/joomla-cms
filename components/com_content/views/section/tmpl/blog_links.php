<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div>
	<strong><?php echo JText::_( 'Read more...' ); ?></strong>
</div>
<ul>
<?php foreach ($this->links as $link) : ?>
	<li>
		<a class="blogsection" href="<?php echo sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$link->id.'&amp;Itemid='.$Itemid); ?>">
			<?php echo $link->title; ?>
		</a>
	</li>
<?php endforeach; ?>
</ul>