<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Document
 */

defined('JPATH_PLATFORM') or die;

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
	 * @param	array $attribs	Associative array of values
	 * @return	string			The output of the script
	 */
	public function render($module, $attribs = array(), $content = null)
	{
		if (!is_object($module))
		{
			$title	= isset($attribs['title']) ? $attribs['title'] : null;

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
		$params = new JRegistry;
		$params->loadJSON($module->params);

		// use parameters from template
		if (isset($attribs['params'])) {
			$template_params = new JRegistry;
			$template_params->loadJSON(html_entity_decode($attribs['params'], ENT_COMPAT, 'UTF-8'));
			$params->merge($template_params);
			$module = clone $module;
			$module->params = (string) $params;
		}

		$contents = '';

		$cachemode = $params->get('cachemode','oldstatic');  // default for compatibility purposes. Set cachemode parameter or use JModuleHelper::moduleCache from within the module instead

		if ($params->get('cache', 0) == 1  && $conf->get('caching') >= 1 && $cachemode != 'id' && $cachemode != 'safeuri')
		{

			// default to itemid creating mehod and workarounds on
			$cacheparams = new stdClass;
			$cacheparams->cachemode = $cachemode;
			$cacheparams->class = 'JModuleHelper';
			$cacheparams->method = 'renderModule';
			$cacheparams->methodparams = array($module, $attribs);

			$contents = JModuleHelper::ModuleCache($module, $params, $cacheparams);

		}
		else {
			$contents = JModuleHelper::renderModule($module, $attribs);
		}

		return $contents;
	}
}
