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

$user       = JFactory::getUser();
$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
	<article class="thumbnail center">
		<?php
		$layout = new JLayoutFile('medialist.thumbnail.delete', JPATH_COMPONENT . '/layouts');
		$data   = array(
			'item'   => $this->_tmp_doc,
			'folder' => $this->state->get('folder'),
			'task'   => 'file.delete',
		);
		echo $layout->render($data);
		?>

		<div class="height-80" onclick="toggleCheckedStatus('<?php echo $this->_tmp_doc->title; ?>');">
			<div class="img-preview" style="height: 80px;">
				<?php echo JHtml::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->name, null, true, true) ? JHtml::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->title, null, true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_doc->name, null, true); ?>
			</div>
		</div>

		<div class="small height-20" style="text-align: center; font-size: small;">
			<?php echo JHtml::_('string.truncate', $this->_tmp_doc->title, 10, false); ?>

			<?php
			$layout = new JLayoutFile('medialist.thumbnail.edit', JPATH_COMPONENT . '/layouts');
			$data   = array(
				'item'   => $this->_tmp_doc,
				'folder' => $this->state->get('folder'),
			);
			echo $layout->render($data);
			?>
		</div>
	</article>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
