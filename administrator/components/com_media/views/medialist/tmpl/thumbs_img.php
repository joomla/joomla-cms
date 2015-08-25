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
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>
	<article class="thumbnail center">
		<?php
		$data   = array(
			'item'   => $this->_tmp_img,
		);
		echo JLayoutHelper::render('medialist.thumbnail.delete', $data);
		?>

		<div class="height-80" onclick="toggleCheckedStatus('<?php echo $this->_tmp_img->title; ?>');">
			<div class="img-preview" style="height: 80px;">
				<?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, JHtml::_('number.bytes', $this->_tmp_img->size))); ?>
			</div>
		</div>

		<div class="small height-20" style="text-align: center; font-size: small;">
			<?php echo JHtml::_('string.truncate', $this->_tmp_img->title, 10, false); ?>

			<a class="img-preview pull-right" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>">
				<span class="icon-zoom-in" style="padding-left: 5px;"></span>
			</a>

			<?php
			$data   = array(
				'item'   => $this->_tmp_img,
				'folder' => $this->state->get('folder'),
			);
			echo JLayoutHelper::render('medialist.thumbnail.edit', $data);
			?>
		</div>
	</article>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
