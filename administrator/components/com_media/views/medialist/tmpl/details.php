<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$params = JComponentHelper::getParams('com_media');
$path   = 'file_path';

JHtml::_('jquery.framework');
JHtml::_('behavior.core');

$doc = JFactory::getDocument();

// Need to override this core function because we use a different form id
$doc->addScriptDeclaration(
	"
		Joomla.isChecked = function( isitchecked, form ) {
			if ( typeof form  === 'undefined' ) {
				form = document.getElementById( 'mediamanager-form' );
			}

			form.boxchecked.value += isitchecked ? 1 : -1;

			// If we don't have a checkall-toggle, done.
			if ( !form.elements[ 'checkall-toggle' ] ) return;

			// Toggle main toggle checkbox depending on checkbox selection
			var c = true,
				i, e, n;

			for ( i = 0, n = form.elements.length; i < n; i++ ) {
				e = form.elements[ i ];

				if ( e.type == 'checkbox' && e.name != 'checkall-toggle' && !e.checked ) {
					c = false;
					break;
				}
			}

			form.elements[ 'checkall-toggle' ].checked = c;
		};
	"
);

$doc->addScriptDeclaration(
	"
		jQuery(document).ready(function($){
			window.parent.document.updateUploader();
			$('.img-preview, .preview').each(function(index, value) {
				$(this).on('click', function(e) {
					window.parent.jQuery('#imagePreviewSrc').attr('src', $(this).attr('href'));
					window.parent.jQuery('#imagePreview').modal('show');
					return false;
				});
			});
			$('.video-preview').each(function(index, value) {
				$(this).unbind('click');
				$(this).on('click', function(e) {
					e.preventDefault();
					window.parent.jQuery('#videoPreview').modal('show');

					var elementInitialised = window.parent.jQuery('#mejsPlayer').attr('src');

					if (!elementInitialised)
					{
						window.parent.jQuery('#mejsPlayer').attr('src', $(this).attr('href'));
						window.parent.jQuery('#mejsPlayer').mediaelementplayer();
					}

					window.parent.jQuery('#mejsPlayer')[0].player.media.setSrc($(this).attr('href'));

					return false;
				});
			});
		});
	"
);
?>
<form target="_parent" action="index.php?option=com_media&amp;tmpl=index&amp;folder=<?php echo rawurlencode($this->state->folder); ?>" method="post" id="mediamanager-form" name="mediamanager-form">
	<div class="muted">
		<p>
			<span class="icon-folder"></span>
			<?php
				echo $params->get($path, 'images'),
					($this->escape($this->state->folder) != '') ? '/' . $this->escape($this->state->folder) : '';
			?>
		</p>
	</div>

	<div class="manager">
		<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<?php if ($this->canDelete) : ?>
					<th width="1%">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
				<?php endif; ?>
				<th width="1%"><?php echo JText::_('JGLOBAL_PREVIEW'); ?></th>
				<th><?php echo JText::_('COM_MEDIA_NAME'); ?></th>
				<th width="15%"><?php echo JText::_('COM_MEDIA_PIXEL_DIMENSIONS'); ?></th>
				<th width="8%"><?php echo JText::_('COM_MEDIA_FILESIZE'); ?></th>

				<?php if ($this->canDelete) : ?>
					<th width="8%">
						<?php echo JText::_('JACTION_DELETE'); ?>
					</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php
				echo $this->loadTemplate('up'),
					$this->loadTemplate('folders'),
					$this->loadTemplate('docs'),
					$this->loadTemplate('videos'),
					$this->loadTemplate('imgs');
			?>
		</tbody>
		</table>
	</div>

	<input type="hidden" name="task" value="list" />
	<input type="hidden" name="username" value="" />
	<input type="hidden" name="password" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
