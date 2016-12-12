<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
?>

<?php foreach ($this->folders as $i => $folder) : ?>
	<?php $link = 'index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=' . $folder->path_relative; ?>
	<tr>
		<?php if ($this->canDelete) : ?>
			<td>
				<?php echo JHtml::_('grid.id', $i, $folder->name, false, 'rm', 'cb-folder'); ?>
			</td>
		<?php endif; ?>
		<td class="imgTotal">
			<a href="<?php echo $link; ?>" target="folderframe"><span class="icon-folder-2"></span></a>
		</td>

		<td class="description">
			<a href="<?php echo $link; ?>" target="folderframe"><?php echo $folder->name; ?></a>
		</td>

		<td>&#160;</td>

		<td>&#160;</td>

		<?php if ($this->canDelete) : ?>
			<td>
				<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=folder.delete&amp;tmpl=index&amp;folder=<?php echo $this->state->folder; ?>&amp;<?php echo JSession::getFormToken(); ?>=1&amp;rm[]=<?php echo $folder->name; ?>" rel="<?php echo $folder->name; ?> :: <?php echo $folder->files + $folder->folders; ?>">
					<span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE'); ?>"></span>
				</a>
			</td>
		<?php endif; ?>
	</tr>
<?php endforeach; ?>
