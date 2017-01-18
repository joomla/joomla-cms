<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php foreach ($this->folders as $i => $folder) : ?>
	<li class="imgOutline thumbnail height-80 width-80 center">
		<?php if ($this->canDelete) : ?>
			<a class="close delete-item" target="_top" href="index.php?option=com_media&amp;task=folder.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $folder->name; ?>" rel="<?php echo $folder->name; ?> :: <?php echo $folder->files + $folder->folders; ?>" title="<?php echo JText::_('JACTION_DELETE'); ?>">&#215;</a>
			<div class="float-left">
				<?php echo JHtml::_('grid.id', $i, $folder->name, false, 'rm', 'cb-folder'); ?>
			</div>
			<div class="clearfix"></div>
		<?php endif; ?>

		<div class="height-50">
			<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $folder->path_relative; ?>" target="folderframe">
				<span class="icon-folder-2"></span>
			</a>
		</div>

		<div class="small">
			<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $folder->path_relative; ?>" target="folderframe">
				<?php echo JHtml::_('string.truncate', $folder->name, 10, false); ?>
			</a>
		</div>
	</li>
<?php endforeach; ?>
