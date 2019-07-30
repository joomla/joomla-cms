<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JHtml::_('bootstrap.tooltip');

$user       = JFactory::getUser();
$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_doc, &$params, 0));
?>

<tr>
	<td>
		<a  title="<?php echo $this->escape($this->_tmp_doc->name); ?>">
			<?php  echo JHtml::_('image', $this->_tmp_doc->icon_16, $this->escape($this->_tmp_doc->title), null, true, true) ? JHtml::_('image', $this->_tmp_doc->icon_16, $this->_tmp_doc->title, array('width' => 16, 'height' => 16), true) : JHtml::_('image', 'media/con_info.png', $this->escape($this->_tmp_doc->title), array('width' => 16, 'height' => 16), true);?> </a>
	</td>
	<td class="description"  title="<?php echo $this->escape($this->_tmp_doc->name); ?>">
		<?php echo $this->escape($this->_tmp_doc->title); ?>
	</td>
	<td>&#160;

	</td>
	<td class="filesize">
		<?php echo JHtml::_('number.bytes', $this->_tmp_doc->size); ?>
	</td>
<?php if ($user->authorise('core.delete', 'com_media')):?>
	<td>
		<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo rawurlencode($this->state->folder); ?>&amp;rm[]=<?php echo $this->escape($this->_tmp_doc->name); ?>" rel="<?php echo $this->escape($this->_tmp_doc->name); ?>"><span class="icon-remove hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JACTION_DELETE');?>"></span></a>
		<input type="checkbox" name="rm[]" value="<?php echo $this->escape($this->_tmp_doc->name); ?>" />
	</td>
<?php endif;?>
</tr>
<?php $dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_doc, &$params, 0));
