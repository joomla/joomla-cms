<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<ul class="mostread<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) : ?>
	<li class="mostread<?php echo $params->get('moduleclass_sfx'); ?>">
		<a href="<?php echo $item->link; ?>" class="mostread<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->text; ?>
		</a>
	</li>
<?php endforeach; ?>
</ul>