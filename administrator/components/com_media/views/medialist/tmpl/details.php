<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();
?>
<form target="_parent" action="index.php?option=com_media&amp;tmpl=index&amp;folder=<?php echo $this->state->folder; ?>" method="post" id="mediamanager-form" name="mediamanager-form">
	<div class="manager">
		<table class="table table-striped table-condensed">
			<thead>
			<tr>
				<?php if ($user->authorise('core.delete', 'com_media')): ?>
					<th colspan="2" width="1%" style="min-width:75px" class="hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
				<?php endif; ?>
				<th colspan="2">
					<?php echo JText::_('COM_MEDIA_NAME'); ?>
				</th>
				<th width="15%">
					<?php echo JText::_('COM_MEDIA_PIXEL_DIMENSIONS'); ?>
				</th>
				<th width="8%">
					<?php echo JText::_('COM_MEDIA_FILESIZE'); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php echo $this->loadTemplate('up'); ?>

			<?php for ($i = 0, $n = count($this->folders); $i < $n; $i++) :
				$this->setFolder($i);
				echo $this->loadTemplate('folder');
			endfor; ?>

			<?php for ($i = 0, $n = count($this->documents); $i < $n; $i++) :
				$this->setDoc($i);
				echo $this->loadTemplate('doc');
			endfor; ?>

			<?php for ($i = 0, $n = count($this->images); $i < $n; $i++) :
				$this->setImage($i);
				echo $this->loadTemplate('img');
			endfor; ?>

			</tbody>
		</table>

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="list" />
		<input type="hidden" name="username" value="" />
		<input type="hidden" name="password" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

<script>
	document.adminForm = document['mediamanager-form'];
</script>