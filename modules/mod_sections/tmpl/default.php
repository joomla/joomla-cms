<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<ul class="sections<?php echo $params->get('moduleclass_sfx'); ?>"><?php
foreach ($list as $item) :
?>
<li>
	<a href="<?php echo sefRelToAbs("index.php?option=com_content&amp;view=section&amp;layout=blog&amp;id=".$item->id."&amp;Itemid=".$Itemid);?>">
		<?php echo $item->title;?>
	</a>
</li>
<?php endforeach; ?>
</ul>