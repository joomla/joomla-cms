<?php
/**
* @version		$Id:tooltips.php 6961 2007-03-15 16:06:53Z tcp $
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

function loadTooltips($selector='.hasTip', $params=array())
{
	static $tips;

	if (!isset($tips)) {
		$tips = array();
	}

	$sig = md5(serialize(array($selector,$params)));
	if (isset($tips[$sig]) && ($tips[$sig])) {
		return;
	}

	// Setup options object
	$options = '{';
	$opt['maxTitleChars']	= (isset($params['maxTitleChars']) && ($params['maxTitleChars'])) ? (int)$params['maxTitleChars'] : 50 ;
	$opt['timeOut']			= (isset($params['timeOut'])) ? (int)$params['timeOut'] : null;
	$opt['showDelay']		= (isset($params['showDelay'])) ? (int)$params['showDelay'] : null;
	$opt['hideDelay']		= (isset($params['hideDelay'])) ? (int)$params['hideDelay'] : null;
	$opt['className']		= (isset($params['className'])) ? $params['className'] : null;
	$opt['fixed']			= (isset($params['fixed']) && ($params['fixed'])) ? 'true' : 'false';
	$opt['onShow']			= (isset($params['onShow'])) ? $params['onShow'] : null;
	$opt['onHide']			= (isset($params['onHide'])) ? $params['onHide'] : null;
	foreach ($opt as $k => $v)
	{
		if ($v) {
			$options .= $k.': '.$v.',';
		}
	}
	if (substr($options, -1) == ',') {
		$options = substr($options, 0, -1);
	}
	$options .= '}';

	// Attach tooltips to document
	$document =& JFactory::getDocument();
	$tooltipInit = '		Window.onDomReady(function(){ var JTooltips = new Tips($$(\''.$selector.'\'), '.$options.'); });';
	$document->addScriptDeclaration($tooltipInit);

	// Set static array
	$tips[$sig] = true;
	return;
}

// Create the default tooltips
loadTooltips();