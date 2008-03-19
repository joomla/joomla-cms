<?php defined('_JEXEC') or die('Restricted access'); ?>
<div>
	<strong><?php echo JText::_( 'More Articles...' ); ?></strong>
</div>
<ul>
<?php
 foreach ($this->links as $link) : ?>
	<li>
		<a class="blogsection" href="<?php echo JRoute::_('index.php?option=com_content&view=article&id='.$link->slug); ?>">
			<?php echo $link->title; ?></a>
	</li>
<?php endforeach; ?>
</ul>
