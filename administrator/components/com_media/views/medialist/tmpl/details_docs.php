<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JHtml::_('bootstrap.tooltip');

$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
?>

<?php foreach ($this->documents as $i => $doc) : ?>
	<?php $dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$doc, &$params, 0)); ?>
	<tr>
		<?php if ($this->canDelete) : ?>
			<td>
				<?php echo JHtml::_('grid.id', $i, $this->escape($doc->name), false, 'rm', 'cb-document'); ?>
			</td>
		<?php endif; ?>

		<td>
			<a title="<?php echo $this->escape($doc->name); ?>">
				<?php echo JHtml::_('image', $doc->icon_16, $this->escape($doc->title), null, true, true) ? JHtml::_('image', $doc->icon_16, $this->escape($doc->title), array('width' => 16, 'height' => 16), true) : JHtml::_('image', 'media/con_info.png', $this->escape($doc->title), array('width' => 16, 'height' => 16), true); ?>
			</a>
		</td>

		<td class="description"  title="<?php echo $this->escape($doc->name); ?>">
			<?php echo $this->escape($doc->title); ?>
		</td>

		<td>&#160;</td>

		<td class="filesize">
			<?php echo JHtml::_('number.bytes', $doc->size); ?>
		</td>

		<?php if ($this->canDelete) : ?>
			<td>
				<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo rawurlencode($this->state->folder); ?>&amp;rm[]=<?php echo $this->escape($doc->name); ?>" rel="<?php echo $this->escape($doc->name); ?>">
					<span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE'); ?>"></span>
				</a>
			</td>
		<?php endif; ?>

	</tr>
	<?php $dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$doc, &$params, 0)); ?>
<?php endforeach; ?>
