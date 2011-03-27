<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$user = JFactory::getUser();
?>
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe">
						<?php echo JHtml::_('image','media/folder.png', JText::_('COM_MEDIA_FOLDER'), array('width' => 80, 'height' => 80, 'border' => 0), true); ?></a>
				</div>
			</div>
			<div class="controls">
			<?php if ($user->authorise('core.delete','com_media')):?>
				<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=folder.delete&amp;tmpl=index&amp;<?php echo JUtility::getToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_folder->name; ?>" rel="<?php echo $this->_tmp_folder->name; ?> :: <?php echo $this->_tmp_folder->files+$this->_tmp_folder->folders; ?>"><?php echo JHtml::_('image','media/remove.png', JText::_('JACTION_DELETE'), array('width' => 16, 'height' => 16, 'border' => 0), true); ?></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_folder->name; ?>" />
			<?php endif;?>
			</div>
			<div class="imginfoBorder">
				<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"><?php echo substr($this->_tmp_folder->name, 0, 10) . (strlen($this->_tmp_folder->name) > 10 ? '...' : ''); ?></a>
			</div>
		</div>
