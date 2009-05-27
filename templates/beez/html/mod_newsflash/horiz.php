<?php // @version $Id: horiz.php 10381 2008-06-01 03:35:53Z pasamio $
defined('_JEXEC') or die;
?>

<?php if (count($list) == 1) :
	$item = $list[0];
	modNewsFlashHelper::renderItem($item, $params, $access);
elseif (count($list) > 1) : ?>
<ul class="horiz<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php foreach ($list as $item) : ?>
	<li>
		<?php modNewsFlashHelper::renderItem($item, $params, $access); ?>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif;
