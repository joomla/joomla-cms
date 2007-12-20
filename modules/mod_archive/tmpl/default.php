<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<ul>
	<?php foreach ($list as $item) : ?>
	<li>
		<a href="<?php echo $item->link; ?>">
			<?php echo $item->text; ?></a>
	</li>
	<?php endforeach; ?>
</ul>