<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();
?>
<article class="thumbnail center">
	<?php
	$layout = new JLayoutFile('medialist.thumbnail.delete', JPATH_COMPONENT . '/layouts');
	$data   = array(
		'item'   => $this->_tmp_folder,
		'folder' => $this->state->get('folder'),
		'task'   => 'folder.delete',
	);
	echo $layout->render($data);
	?>

	<div class="height-80">
		<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe">
			<span class="icon-folder-2" style="font-size:300%;"></span>
		</a>
	</div>

	<div class="small">
		<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"><?php echo JHtml::_('string.truncate', $this->_tmp_folder->name, 10, false); ?></a>
	</div>
</article>
