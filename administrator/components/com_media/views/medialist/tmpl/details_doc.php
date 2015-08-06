<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JHtml::_('bootstrap.tooltip');

<<<<<<< HEAD
$user       = JFactory::getUser();
$params     = new JRegistry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
<tr>
	<?php
	$data = array(
		'item' => $this->_tmp_doc,
	);
	echo JLayoutHelper::render('medialist.detail.delete', $data);
	?>
	<td>
		<a title="<?php echo $this->_tmp_doc->name; ?>">
			<?php echo JHtml::_('image', $this->_tmp_doc->icon_16, $this->_tmp_doc->title, null, true, true) ? JHtml::_('image', $this->_tmp_doc->icon_16, $this->_tmp_doc->title, array('width' => 16, 'height' => 16), true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_doc->title, array('width' => 16, 'height' => 16), true); ?> </a>
	</td>
	<td class="description" title="<?php echo $this->_tmp_doc->name; ?>">
=======
$user = JFactory::getUser();
$params = new Registry;
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
<tr>
	<td>
		<a  title="<?php echo $this->_tmp_doc->name; ?>">
			<?php  echo JHtml::_('image', $this->_tmp_doc->icon_16, $this->_tmp_doc->title, null, true, true) ? JHtml::_('image', $this->_tmp_doc->icon_16, $this->_tmp_doc->title, array('width' => 16, 'height' => 16), true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_doc->title, array('width' => 16, 'height' => 16), true);?> </a>
	</td>
	<td class="description"  title="<?php echo $this->_tmp_doc->name; ?>">
>>>>>>> upstream/3.5-dev
		<?php echo $this->_tmp_doc->title; ?>
	</td>
	<td>&#160;

	</td>
	<td class="filesize">
		<?php echo JHtml::_('number.bytes', $this->_tmp_doc->size); ?>
	</td>
<<<<<<< HEAD

=======
<?php if ($user->authorise('core.delete', 'com_media')):?>
	<td>
		<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_doc->name; ?>" rel="<?php echo $this->_tmp_doc->name; ?>"><span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE');?>"></span></a>
		<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_doc->name; ?>" />
	</td>
<?php endif;?>
>>>>>>> upstream/3.5-dev
</tr>
<?php
	$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
