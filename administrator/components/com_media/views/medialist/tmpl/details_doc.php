<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

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
		<?php echo $this->_tmp_doc->title; ?>
	</td>
	<td>&#160;

	</td>
	<td class="filesize">
		<?php echo JHtml::_('number.bytes', $this->_tmp_doc->size); ?>
	</td>

</tr>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
