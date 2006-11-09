<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<ul class="relateditems<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) :	?>
<li>
	<a href="<?php echo sefRelToAbs("index.php?option=com_content&amp;view=article&amp;id=$item->id&amp;Itemid=$item->itemid"); ?>">
		<?php if ($showDate) echo $item->created . " - "; ?>
		<?php echo $item->title; ?>
	</a>
</li>
<?php endforeach; ?>
</ul>