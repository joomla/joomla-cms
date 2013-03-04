<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<div class="item">
	<a href="index.php?option=com_media&amp;view=imagesList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>&amp;asset=<?php echo JRequest::getCmd('asset');?>&amp;author=<?php echo JRequest::getCmd('author');?>">
		<?php echo JHtml::_('image', 'media/folder.gif', $this->_tmp_folder->name, array('height' => 80, 'width' => 80), true); ?>
		<span><?php echo $this->_tmp_folder->name; ?></span></a>
</div>
