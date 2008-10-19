<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div>
	<strong><?php echo JText::_( 'More Articles...' ); ?></strong>
</div>
<ul>
<?php foreach ($this->links as $link) : ?>
	<li>
		<a class="blogsection" href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($link->slug, $link->catslug, $link->sectionid)); ?>">
			<?php echo $link->title; ?></a>
	</li>
<?php endforeach; ?>
</ul>