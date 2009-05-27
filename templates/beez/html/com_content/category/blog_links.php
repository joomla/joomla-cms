<?php // @version $Id: blog_links.php 10770 2008-08-23 11:36:27Z willebil $
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
