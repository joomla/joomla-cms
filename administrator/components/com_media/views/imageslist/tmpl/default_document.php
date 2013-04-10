<?php
/**
 * @package		Jokte.Administrator
 * @subpackage	com_media
 * @copyleft	Copyleft 2012 - 2013 Comunidad Juuntos & Jokte!
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
				<?php echo JHtml::_('image', $this->baseURL.'/'.$this->_tmp_doc->icon_32, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_doc->title, MediaHelper::parseSize($this->_tmp_doc->size)), array('width' => $this->_tmp_doc->width_60, 'height' => $this->_tmp_doc->height_60)); ?>
				<span title="<?php echo $this->_tmp_doc->name; ?>"><?php echo $this->_tmp_doc->title; ?></span></a>
		</div>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
