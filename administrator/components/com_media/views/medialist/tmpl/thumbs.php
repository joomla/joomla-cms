<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$params = JComponentHelper::getParams('com_media');
$path = 'file_path';
?>
<form target="_parent" action="index.php?option=com_media&amp;tmpl=index&amp;folder=<?php echo $this->state->folder; ?>" method="post" id="adminForm" name="adminForm">
	<div class="muted">
		<p>
			<span class="icon-folder"></span>
			<?php if ($this->state->folder != '') : ?>
				<?php echo JText::_('JGLOBAL_ROOT') . ': ' . $params->get($path, 'images') . '/' . $this->state->folder; ?>
			<?php else : ?>
				<?php echo JText::_('JGLOBAL_ROOT') . ': ' . $params->get($path, 'images'); ?>
			<?php endif; ?>
		</p>
	</div>

	<ul class="manager thumbnails">
		<?php if ($this->state->folder != '') : ?>
			<li class="span2">
				<?php echo $this->loadTemplate('up'); ?>
			</li>
		<?php endif; ?>

		<?php for ($i = 0, $n = count($this->folders); $i < $n; $i++) : ?>
			<li class="span2">
				<?php $this->setFolder($i); ?>
				<?php echo $this->loadTemplate('folder'); ?>
			</li>
		<?php endfor; ?>

		<?php for ($i = 0, $n = count($this->documents); $i < $n; $i++) : ?>
			<li class="span2">
				<?php $this->setDoc($i); ?>
				<?php echo $this->loadTemplate('doc'); ?>
			</li>
		<?php endfor; ?>

		<?php for ($i = 0, $n = count($this->images); $i < $n; $i++) : ?>
			<li class="span2">
				<?php $this->setImage($i); ?>
				<?php echo $this->loadTemplate('img'); ?>
			</li>
		<?php endfor; ?>

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="username" value="" />
		<input type="hidden" name="password" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</ul>
</form>
