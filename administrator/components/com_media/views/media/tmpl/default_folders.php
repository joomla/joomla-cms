<?php defined('_JEXEC') or die('Restricted access'); ?>
<ul>
<?php foreach ($this->folders['children'] as $folder) : ?>
	<li><a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $folder['data']->relative; ?>" target="folderframe"><?php echo $folder['data']->name; ?></a><?php echo $this->getFolderLevel($folder); ?></li>
<?php endforeach; ?>
</ul>
