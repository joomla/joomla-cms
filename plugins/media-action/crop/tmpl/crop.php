<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.resize
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// @todo move to local scripts
JFactory::getDocument()->addStyleSheet('//cdnjs.cloudflare.com/ajax/libs/cropperjs/0.8.1/cropper.min.css');
JFactory::getDocument()->addScript('//cdnjs.cloudflare.com/ajax/libs/cropperjs/0.8.1/cropper.js');

JFactory::getDocument()->addScriptDeclaration("
EventBus.addEventListener('onActivate', function(e, context, imageElement){ 
if (context != 'crop') {
	return;
}

var cropper = new Cropper(imageElement, {
	minContainerHeight: imageElement.offsetHeight,
	crop: function(e) {    
		document.getElementById('jform_crop_x').value = e.detail.x;
		document.getElementById('jform_crop_y').value = e.detail.y;
		document.getElementById('jform_crop_width').value = e.detail.width;
		document.getElementById('jform_crop_height').value = e.detail.height;
	}
});

document.getElementById('jform_crop_x').value = 0;
document.getElementById('jform_crop_y').value = 0;
document.getElementById('jform_crop_width').value = imageElement.offsetWidth;
document.getElementById('jform_crop_height').value = imageElement.offsetHeight;

imageElement.cropper = cropper;
});

EventBus.addEventListener('onDeactivate', function(e, context, imageElement){
if (context != 'crop' || !imageElement.cropper) {
	return;
}

imageElement.cropper.destroy();
});
");
