<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageinstaller
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('jquery.token');

JText::script('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_UNKNOWN');
JText::script('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_EMPTY');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbuttonpackage = function()
	{
		var form = document.getElementById("adminForm");

		// do field validation 
		if (form.install_package.value == "")
		{
			alert("' . JText::_('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE', true) . '");
		}
		else
		{
			JoomlaInstaller.showLoading();
			form.installtype.value = "upload"
			form.submit();
		}
	};
');

// Drag and Drop installation scripts
$token = JSession::getFormToken();
$return = JFactory::getApplication()->input->getBase64('return');

// Drag-drop installation
JFactory::getDocument()->addScriptDeclaration(
<<<JS
	jQuery(document).ready(function($) {
		if (typeof FormData === 'undefined') {
			$('#legacy-uploader').show();
			$('#uploader-wrapper').hide();
			return;
		}

		var uploading = false;
		var dragZone  = $('#dragarea');
		var fileInput = $('#install_package');
		var button    = $('#select-file-button');
		var url       = 'index.php?option=com_installer&task=install.ajax_upload';
		var returnUrl = $('#installer-return').val();
		var actions   = $('.upload-actions');
		var progress  = $('.upload-progress');
		var progressBar = progress.find('.bar');
		var percentage = progress.find('.uploading-number');

		if (returnUrl) {
			url += '&return=' + returnUrl;
		}

		button.on('click', function(e) {
			fileInput.click();
		});

		fileInput.on('change', function (e) {
			if (uploading) {
				return;
			}

			Joomla.submitbuttonpackage();
		});

		dragZone.on('dragenter', function(e) {
			e.preventDefault();
			e.stopPropagation();

			dragZone.addClass('hover');

			return false;
		});

		// Notify user when file is over the drop area
		dragZone.on('dragover', function(e) {
			e.preventDefault();
			e.stopPropagation();

			dragZone.addClass('hover');

			return false;
		});

		dragZone.on('dragleave', function(e) {
			e.preventDefault();
			e.stopPropagation();
			dragZone.removeClass('hover');

			return false;
		});

		dragZone.on('drop', function(e) {
			e.preventDefault();
			e.stopPropagation();

			dragZone.removeClass('hover');

			if (uploading) {
				return;
			}

			var files = e.originalEvent.target.files || e.originalEvent.dataTransfer.files;

			if (!files.length) {
				return;
			}

			var file = files[0];

			var data = new FormData;
			data.append('install_package', file);
			data.append('installtype', 'upload');

			dragZone.attr('data-state', 'uploading');
			uploading = true;

			$.ajax({
				url: url,
				data: data,
				type: 'post',
				processData: false,
				cache: false,
				contentType: false,
				xhr: function () {
					var xhr = new window.XMLHttpRequest();

					progressBar.css('width', 0);
					progressBar.attr('aria-valuenow', 0);
					percentage.text(0);

					// Upload progress
					xhr.upload.addEventListener("progress", function (evt) {
						if (evt.lengthComputable) {
							var percentComplete = evt.loaded / evt.total;
							var number = Math.round(percentComplete * 100);
							progressBar.css('width', number + '%');
							progressBar.attr('aria-valuenow', number);
							percentage.text(number);

							if (number === 100) {
								dragZone.attr('data-state', 'installing');
							}
						}
					}, false);

					return xhr;
				}
			})
			.done(function (res) {
				// Handle extension fatal error
				if (!res || (!res.success && !res.data)) {
					showError(res);
					return;
				}

				// Always redirect that can show message queue from session 
				if (res.data.redirect) {
					location.href = res.data.redirect;
				} else {
					location.href = 'index.php?option=com_installer&view=install';
				}
			}).error(function (error) {
				uploading = false;

				if (error.status === 200) {
					var res = error.responseText || error.responseJSON;
					showError(res);
				} else {
					showError(error.statusText);
				}
			});

			function showError(res) {
				dragZone.attr('data-state', 'pending');

				var message = Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_UNKNOWN');

				if (res == null) {
					message = Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_EMPTY');
				} else if (typeof res === 'string') {
					// Let's remove unnecessary HTML
					message = res.replace(/(<([^>]+)>|\s+)/g, ' ');
				} else if (res.message) {
					message = res.message;
				}

				Joomla.renderMessages({error: [message]});
			}
		});
	});
JS
);

JFactory::getDocument()->addStyleDeclaration(
<<<CSS
	#dragarea {
		background-color: #fafbfc;
		border: 1px dashed #999;
		box-sizing: border-box;
		padding: 5% 0;
		transition: all 0.2s ease 0s;
		width: 100%;
	}

	#dragarea p.lead {
		color: #999;
	}

	#upload-icon {
		font-size: 48px;
		width: auto;
		height: auto;
		margin: 0;
		line-height: 175%;
		color: #999;
		transition: all .2s;
	}

	#dragarea.hover {
		border-color: #666;
		background-color: #eee;
	}

	#dragarea.hover #upload-icon,
	#dragarea p.lead {
		color: #666;
	}

	 .upload-progress, .install-progress {
		width: 50%;
		margin: 5px auto;
	 }

	/* Default transition (.3s) is too slow, progress will not run to 100% */
	.upload-progress .progress .bar {
		-webkit-transition: width .1s;
		-moz-transition: width .1s;
		-o-transition: width .1s;
		transition: width .1s;
	}

	#dragarea[data-state=pending] .upload-progress {
		display: none;
	}

	#dragarea[data-state=pending] .install-progress {
		display: none;
	}

	#dragarea[data-state=uploading] .install-progress {
		display: none;
	}

	#dragarea[data-state=uploading] .upload-actions {
		display: none;
	}

	#dragarea[data-state=installing] .upload-progress {
		display: none;
	}

	#dragarea[data-state=installing] .upload-actions {
		display: none;
	}
CSS
);

$maxSize = JFilesystemHelper::fileUploadMaxSize();
?>
<legend><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>

<div id="uploader-wrapper">
	<div id="dragarea" data-state="pending">
		<div id="dragarea-content" class="text-center">
			<p>
				<span id="upload-icon" class="icon-upload" aria-hidden="true"></span>
			</p>
			<div class="upload-progress">
				<div class="progress progress-striped active">
					<div class="bar bar-success"
						style="width: 0;"
						role="progressbar"
						aria-valuenow="0"
						aria-valuemin="0"
						aria-valuemax="100"
					></div>
				</div>
				<p class="lead">
					<span class="uploading-text">
						<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOADING'); ?>
					</span>
					<span class="uploading-number">0</span><span class="uploading-symbol">%</span>
				</p>
			</div>
			<div class="install-progress">
				<div class="progress progress-striped active">
					<div class="bar" style="width: 100%;"></div>
				</div>
				<p class="lead">
					<span class="installing-text">
						<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_INSTALLING'); ?>
					</span>
				</p>
			</div>
			<div class="upload-actions">
				<p class="lead">
					<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_DRAG_FILE_HERE'); ?>
				</p>
				<p>
					<button id="select-file-button" type="button" class="btn btn-success">
						<span class="icon-copy" aria-hidden="true"></span>
						<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_SELECT_FILE'); ?>
					</button>
				</p>
				<p>
					<?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
				</p>
			</div>
		</div>
	</div>
</div>

<div id="legacy-uploader" style="display: none;">
	<div class="control-group">
		<label for="install_package" class="control-label"><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
		<div class="controls">
			<input class="input_box" id="install_package" name="install_package" type="file" size="57" /><br>
			<?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
		</div>
	</div>
	<div class="form-actions">
		<button class="btn btn-primary" type="button" id="installbutton_package" onclick="Joomla.submitbuttonpackage()">
			<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_AND_INSTALL'); ?>
		</button>
	</div>

	<input id="installer-return" name="return" type="hidden" value="<?php echo $return; ?>" />
	<input id="installer-token" name="return" type="hidden" value="<?php echo $token; ?>" />
</div>
