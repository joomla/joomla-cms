<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.resize
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addStyleDeclaration("
.rotate90  { transform: rotate(90deg);  }
.rotate180 { transform: rotate(180deg); }
.rotate270 { transform: rotate(270deg); }
");

JFactory::getDocument()->addScriptDeclaration("
EventBus.addEventListener('onActivate', function(e, context, imageElement){
if (context != 'rotate') {
	return;
}

document.querySelector('label[for=\"jform_rotate_options0\"]').onclick = function() {
	var angle = imageElement.angle ? imageElement.angle + 90 : 90;
	if (angle > 270 ) {
		angle = 0;
	}
	imageElement.angle = angle;
    imageElement.className = 'rotate' + angle;
}
document.querySelector('label[for=\"jform_rotate_options1\"]').onclick = function() {
	var angle = imageElement.angle ? imageElement.angle - 90 : 270;
	if (angle < 0 ) {
		angle = 270;
	}
	imageElement.angle = angle;
    imageElement.className = 'rotate' + angle;
}
});

EventBus.addEventListener('onDeactivate', function(e, context, imageElement){
if (context != 'rotate') {
	return;
}

document.getElementById('jform_rotate_options0').onclick = null
document.getElementById('jform_rotate_options1').onclick = null
});
");
