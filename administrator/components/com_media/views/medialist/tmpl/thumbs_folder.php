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
<<<<<<< HEAD
<article class="thumbnail center">
	<?php
	$data   = array(
		'item'   => $this->_tmp_folder,
	);
	echo JLayoutHelper::render('medialist.thumbnail.delete', $data);
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
=======
<li class="imgOutline thumbnail height-80 width-80 center">
	<?php if ($user->authorise('core.delete', 'com_media')):?>
		<a class="close delete-item" target="_top" href="index.php?option=com_media&amp;task=folder.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_folder->name; ?>" rel="<?php echo $this->_tmp_folder->name; ?> :: <?php echo $this->_tmp_folder->files + $this->_tmp_folder->folders; ?>" title="<?php echo JText::_('JACTION_DELETE');?>">&#215;</a>
		<input class="pull-left" type="checkbox" name="rm[]" value="<?php echo $this->_tmp_folder->name; ?>" />
		<div class="clearfix"></div>
	<?php endif;?>
	<div class="height-50">
		<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe">
			<span class="icon-folder-2"></span>
		</a>
	</div>
	<div class="small">
		<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"><?php echo JHtml::_('string.truncate', $this->_tmp_folder->name, 10, false); ?></a>
	</div>
</li>
>>>>>>> upstream/3.5-dev
