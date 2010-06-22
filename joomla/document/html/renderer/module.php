<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * JDocument Module renderer
 *
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentRendererModule extends JDocumentRenderer
{
	/**
	 * Renders a module script and returns the results as a string
	 *
	 * @param	string $name	The name of the module to render
	 * @param	array $params	Associative array of values
	 * @return	string			The output of the script
	 */
	public function render($module, $params = array(), $content = null)
	{
		if (!is_object($module))
		{
			$title	= isset($params['title']) ? $params['title'] : null;

			$module = JModuleHelper::getModule($module, $title);

			if (!is_object($module))
			{
				if (is_null($content)) {
					return '';
				}
				else {
					/**
					 * If module isn't found in the database but data has been pushed in the buffer
					 * we want to render it
					 */
					$tmp = $module;
					$module = new stdClass();
					$module->params = null;
					$module->module = $tmp;
					$module->id = 0;
					$module->user = 0;
				}
			}
		}

		// get the user and configuration object
		//$user = JFactory::getUser();
		$conf = JFactory::getConfig();

		// set the module content
		if (!is_null($content)) {
			$module->content = $content;
		}

		//get module parameters
		$mod_params = new JRegistry;
		$mod_params->loadJSON($module->params);

		$contents = '';


		$cachemode = $mod_params->get('cachemode','oldstatic');  // default for compatibility purposes. Set cachemode parameter or use JModuleHelper::moduleCache from within the module instead

		if ($mod_params->get('cache', 0) == 1  && $conf->get('caching') && $cachemode != 'id' && $cachemode != 'safeuri')
		{

			// default to itemid creating mehod and workarounds on
			$cacheparams = new stdClass;
			$cacheparams->cachemode = $cachemode;
			$cacheparams->class = 'JModuleHelper';
			$cacheparams->method = 'renderModule';
			$cacheparams->methodparams = array($module, $params);

			$contents = JModuleHelper::ModuleCache($module, $mod_params,$cacheparams);

		}
		else {
			$contents = JModuleHelper::renderModule($module, $params);
		}

		return $contents;
	}
}
