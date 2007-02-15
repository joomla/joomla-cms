<?php defined('_JEXEC') or die('Restricted access'); ?>
<div>
	<strong><?php echo JText::_( 'Read more...' ); ?></strong>
</div>
<ul>
<?php
 foreach ($this->links as $link) : ?>
	<li>
		<a class="blogsection" href="<?php echo JRoute::_('index.php?option=com_content&view=article&id='.$link->id.'&Itemid='.$Itemid); ?>">
			<?php echo $link->title; ?>
		</a>
	</li>
<?php endforeach; ?>
</ul>