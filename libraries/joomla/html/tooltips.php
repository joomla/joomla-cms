<?php
/**
* @version		$Id: pane.php 6138 2007-01-02 03:44:18Z eddiea $
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

$tooltipInit = '
		Window.onDomReady(function(){
			var JTooltips = new Tips($S(\'.hasTip\'), {
				maxTitleChars: 50,
				maxOpacity: .9,
			});
		});';

$document =& JFactory::getDocument();
$document->addScript('../includes/js/mootools.js');
$document->addScriptDeclaration($tooltipInit);
?>