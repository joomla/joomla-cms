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

JHtml::_('bootstrap.tooltip');
?>
<tr>
<<<<<<< HEAD
	<?php
	$data = array(
		'item' => $this->_tmp_img,
	);
	echo JLayoutHelper::render('medialist.detail.delete', $data);
	?>
	<td class="imgTotal">
		<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe">
			<i class="icon-folder-2"></i></a>
=======
	<td class="imgTotal">
		<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe">
			<span class="icon-folder-2"></span></a>
>>>>>>> upstream/3.5-dev
	</td>
	<td class="description">
		<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"><?php echo $this->_tmp_folder->name; ?></a>
	</td>
	<td>&#160;

	</td>
	<td>&#160;

	</td>
<<<<<<< HEAD
=======
	<?php if ($user->authorise('core.delete', 'com_media')):?>
		<td>
			<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=folder.delete&amp;tmpl=index&amp;folder=<?php echo $this->state->folder; ?>&amp;<?php echo JSession::getFormToken(); ?>=1&amp;rm[]=<?php echo $this->_tmp_folder->name; ?>" rel="<?php echo $this->_tmp_folder->name; ?>' :: <?php echo $this->_tmp_folder->files + $this->_tmp_folder->folders; ?>"><span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE');?>"></span></a>
			<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_folder->name; ?>" />
		</td>
	<?php endif;?>
>>>>>>> upstream/3.5-dev
</tr>
