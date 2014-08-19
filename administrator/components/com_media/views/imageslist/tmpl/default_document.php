<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$params = new JRegistry;
$dispatcher	= JDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
		<div class="item">
			<a href="javascript:ImageManager.populateFields('<?php echo $this->_tmp_doc->path_relative; ?>')" title="<?php echo $this->_tmp_doc->name; ?>" >
				<?php echo JHtml::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->name, null, true, true) ? JHtml::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->title, NULL, true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_doc->name, NULL, true) ; ?>
				<span title="<?php echo $this->_tmp_doc->name; ?>"><?php echo $this->_tmp_doc->title; ?></span></a>
		</div>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
