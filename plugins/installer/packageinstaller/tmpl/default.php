<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageinstaller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

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
$text = JText::_('PLG_INSTALLER_PACKAGEINSTALLER_DRAG_FILE_HERE');
JText::script('PLG_INSTALLER_PACKAGEINSTALLER_DRAG_ERR_UNSUPPORTEDBROWSER');
$return = JFactory::getApplication()->input->getBase64('return');

// Drag-drop installation
JFactory::getDocument()->addScriptDeclaration(
<<<JS
    jQuery(document).ready(function($) {
        var dragZone   = $('body');
        var tabContent = $('#package');
        var cover      = $('<div id="dragarea" style="display: none;"></div>');
        var url        = 'index.php?option=com_installer&task=install.ajax_upload';
        var returnUrl  = '{$return}';

        if (returnUrl) {
            url += '&return=' + returnUrl;
        }

        // Create drag cover first
        dragZone.append(cover);

        dragZone.on('dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (!tabContent.hasClass('active')) {
                return;
            }
            
            cover.fadeIn();
            
            return false;
        });

        // Notify user when file is over the drop area
        dragZone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!tabContent.hasClass('active')) {
                return;
            }

            cover.fadeIn();

            return false;
        });

        cover.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            cover.fadeOut();

            return false;
        });

        dragZone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!tabContent.hasClass('active')) {
                return;
            }

            if (typeof FormData === 'undefined') {
                Joomla.renderMessages({'error': [Joomla.JText._("COM_INSTALLER_DRAG_ERR_UNSUPPORTEDBROWSER")]});
                return;
            }

            cover.fadeOut();

            var files = e.originalEvent.target.files || e.originalEvent.dataTransfer.files;

            if (!files.length) {
                return;
            }

            var file = files[0];

            var data = new FormData;
            data.append('install_package', file);
            data.append('installtype', 'upload');
            data.append('{$token}', 1);

            JoomlaInstaller.showLoading();
            
            $.ajax({
                url: url,
                data: data,
                type: 'post',
                processData: false,
                cache: false,
                contentType: false
            }).done(function (res) {
                if (res.success) {
                    if (res.data.redirect) {
                        location.href = res.data.redirect;
                    } else {
                        location.href = 'index.php?option=com_installer&view=install';
                    }
                } else {
                    JoomlaInstaller.hideLoading();
                    alert(res.message);
                }
            }).error (function (error) {
                JoomlaInstaller.hideLoading();
                alert(error.statusText);
            });
        });
    });
JS
);

JFactory::getDocument()->addStyleDeclaration(
<<<CSS
    #dragarea {
        display: block;
        background: rgba(255, 255, 255, .8);
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        opacity: 0.8;
        -ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity = 80);
        filter: alpha(opacity = 80);
        overflow: hidden;
    }

    #dragarea::before {
        /* Use CSS to inject text since a child element will trigger dragleave event */
        content: "{$text}";
        width: 100%;
        display: block;
        font-size: 36px;
        text-align: center;
        position: absolute;
        top: 50%;
    }
CSS
);
?>
<legend><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>
<p class="lead"><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_DRAG_FILE_NOTICE'); ?></p>
<div class="control-group">
	<label for="install_package" class="control-label"><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
	<div class="controls">
		<input class="input_box" id="install_package" name="install_package" type="file" size="57" /><br>
		<?php $maxSize = JHtml::_('number.bytes', JUtility::getMaxUploadSize()); ?>
		<?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
	</div>
</div>
<div class="form-actions">
	<button class="btn btn-primary" type="button" id="installbutton_package" onclick="Joomla.submitbuttonpackage()">
		<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_AND_INSTALL'); ?>
	</button>
</div>
