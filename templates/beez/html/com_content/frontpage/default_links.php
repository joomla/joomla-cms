<?php // @version $Id: default_links.php 10381 2008-06-01 03:35:53Z pasamio $
defined('_JEXEC') or die;
?>

<h2>
	<?php echo JText::_('More Articles...'); ?>
</h2>

<ul>
	<?php foreach ($this->links as $link) : ?>
	<li>
		<a class="blogsection" href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($link->slug, $link->catslug, $link->sectionid)); ?>">
			<?php echo $link->title; ?></a>
	</li>
	<?php endforeach; ?>
</ul>
