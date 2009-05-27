<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

$mainframe->registerEvent('onPrepareContent', 'plgContentLoadModule');

/**
* Plugin that loads module positions within content
 */
function plgContentLoadModule(&$row, &$params, $page=0)
{
	$db = &JFactory::getDbo();
	// simple performance check to determine whether bot should process further
	if (JString::strpos($row->text, 'loadposition') === false) {
		return true;
	}

	// Get plugin info
	$plugin = &JPluginHelper::getPlugin('content', 'loadmodule');

 	// expression to search for
 	$regex = '/{loadposition\s*.*?}/i';

 	$pluginParams = new JParameter($plugin->params);

	// check whether plugin has been unpublished
	if (!$pluginParams->get('enabled', 1)) {
		$row->text = preg_replace($regex, '', $row->text);
		return true;
	}

 	// find all instances of plugin and put in $matches
	preg_match_all($regex, $row->text, $matches);

	// Number of plugins
 	$count = count($matches[0]);

 	// plugin only processes if there are any instances of the plugin in the text
 	if ($count) {
		// Get plugin parameters
	 	$style	= $pluginParams->def('style', -2);

 		plgContentProcessPositions($row, $matches, $count, $regex, $style);
	}
}

function plgContentProcessPositions (&$row, &$matches, $count, $regex, $style)
{
 	for ($i=0; $i < $count; $i++)
	{
 		$load = str_replace('loadposition', '', $matches[0][$i]);
 		$load = str_replace('{', '', $load);
 		$load = str_replace('}', '', $load);
 		$load = trim($load);

		$modules	= plgContentLoadPosition($load, $style);
		$row->text 	= str_replace($matches[0][$i], $modules, $row->text);
 	}

  	// removes tags without matching module positions
	$row->text = preg_replace($regex, '', $row->text);
}

function plgContentLoadPosition($position, $style=-2)
{
	$document	= &JFactory::getDocument();
	$renderer	= $document->loadRenderer('module');
	$params		= array('style'=>$style);

	$contents = '';
	foreach (JModuleHelper::getModules($position) as $mod)  {
		$contents .= $renderer->render($mod, $params);
	}
	return $contents;
}