<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
<form target="_parent" action="index.php?option=com_media&amp;tmpl=index&amp;folder=<?php echo $this->state->folder; ?>" method="post" id="mediamanager-form" name="mediamanager-form">
	<div class="muted breadcrumbs">
		<p>
			<span class="icon-folder"></span>
			<?php
				echo JText::_('JGLOBAL_ROOT'), ': ',
					$params->get($path, 'images'),
					($this->state->folder != '') ? ' / ' . $this->state->folder : ' ';
			?>
		</p>
	</div>

	<div>
		<label class="checkbox btn">
			<?php echo JHtml::_('grid.checkall'); ?>
			<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>
		</label>
	</div>

	<ul class="manager thumbnails thumbnails-media">
		<?php
			echo $this->loadTemplate('up'),
				$this->loadTemplate('folders'),
				$this->loadTemplate('docs'),
				$this->loadTemplate('videos'),
				$this->loadTemplate('imgs');
		?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="username" value="" />
		<input type="hidden" name="password" value="" />
		<input type="hidden" name="boxchecked" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</ul>
</form>
