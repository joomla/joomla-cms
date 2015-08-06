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

<<<<<<< HEAD
$user       = JFactory::getUser();
$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
	<article class="thumbnail center">
		<?php
		$data   = array(
			'item'   => $this->_tmp_doc,
		);
		echo JLayoutHelper::render('medialist.thumbnail.delete', $data);
		?>

		<div class="height-80" onclick="toggleCheckedStatus('<?php echo $this->_tmp_doc->title; ?>');">
			<div class="img-preview" style="height: 80px;">
				<?php echo JHtml::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->name, null, true, true) ? JHtml::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->title, null, true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_doc->name, null, true); ?>
			</div>
		</div>

		<div class="small height-20" style="text-align: center; font-size: small;">
			<?php echo JHtml::_('string.truncate', $this->_tmp_doc->title, 10, false); ?>

			<?php
			$data   = array(
				'item'   => $this->_tmp_doc,
				'folder' => $this->state->get('folder'),
			);
			echo JLayoutHelper::render('medialist.thumbnail.edit', $data);
			?>
		</div>
	</article>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
=======
$user = JFactory::getUser();
$params = new Registry;
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
<li class="imgOutline thumbnail height-80 width-80 center">
	<?php if ($user->authorise('core.delete', 'com_media')):?>
		<a class="close delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_doc->name; ?>" rel="<?php echo $this->_tmp_doc->name; ?>" title="<?php echo JText::_('JACTION_DELETE');?>">&#215;</a>
		<input class="pull-left" type="checkbox" name="rm[]" value="<?php echo $this->_tmp_doc->name; ?>" />
		<div class="clearfix"></div>
	<?php endif;?>
	<div class="height-50">
		<a style="display: block; width: 100%; height: 100%" title="<?php echo $this->_tmp_doc->name; ?>" >
			<?php echo JHtml::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->name, null, true, true) ? JHtml::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->title, null, true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_doc->name, null, true); ?></a>
	</div>
	<div class="small" title="<?php echo $this->_tmp_doc->name; ?>" >
		<?php echo JHtml::_('string.truncate', $this->_tmp_doc->name, 10, false); ?>
	</div>
</li>
<?php
	$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
>>>>>>> upstream/3.5-dev
