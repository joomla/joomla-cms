<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JHtml::_('bootstrap.tooltip');

$params = new Registry;
?>

<?php foreach ($this->documents as $i => $doc) : ?>
	<?php JFactory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_media.file', &$doc, &$params)); ?>
	<tr>
		<?php if ($this->canDelete) : ?>
			<td>
				<?php echo JHtml::_('grid.id', $i, $doc->name, false, 'rm', 'cb-document'); ?>
			</td>
		<?php endif; ?>

		<td>
			<a title="<?php echo $doc->name; ?>">
				<?php echo JHtml::_('image', $doc->icon_16, $doc->title, null, true, true) ? JHtml::_('image', $doc->icon_16, $doc->title, array('width' => 16, 'height' => 16), true) : JHtml::_('image', 'media/con_info.png', $doc->title, array('width' => 16, 'height' => 16), true); ?>
			</a>
		</td>

		<td class="description"  title="<?php echo $doc->name; ?>">
			<?php echo $doc->title; ?>
		</td>

		<td>&#160;</td>

		<td class="filesize">
			<?php echo JHtml::_('number.bytes', $doc->size); ?>
		</td>

		<?php if ($this->canDelete) : ?>
			<td>
				<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $doc->name; ?>" rel="<?php echo $doc->name; ?>">
					<span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE'); ?>"></span>
				</a>
			</td>
		<?php endif; ?>

	</tr>
	<?php JFactory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_media.file', &$doc, &$params)); ?>
<?php endforeach; ?>
