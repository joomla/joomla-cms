<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
?>

<?php foreach ($this->documents as $i => $doc) : ?>
	<?php $dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$doc, &$params)); ?>
	<li class="imgOutline thumbnail height-80 width-80 center">
		<?php if ($this->canDelete) : ?>
			<a class="close delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $doc->name; ?>" rel="<?php echo $doc->name; ?>" title="<?php echo JText::_('JACTION_DELETE'); ?>">&#215;</a>
			<div class="pull-left">
				<?php echo JHtml::_('grid.id', $i, $doc->name, false, 'rm', 'cb-document'); ?>
			</div>
			<div class="clearfix"></div>
		<?php endif; ?>

		<div class="height-50">
			<a style="display: block; width: 100%; height: 100%" title="<?php echo $doc->name; ?>" >
				<?php echo JHtml::_('image', $doc->icon_32, $doc->name, null, true, true) ? JHtml::_('image', $doc->icon_32, $doc->title, null, true) : JHtml::_('image', 'media/con_info.png', $doc->name, null, true); ?>
			</a>
		</div>

		<div class="small" title="<?php echo $doc->name; ?>" >
			<?php echo JHtml::_('string.truncate', $doc->name, 10, false); ?>
		</div>
	</li>
	<?php $dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$doc, &$params)); ?>
<?php endforeach; ?>
