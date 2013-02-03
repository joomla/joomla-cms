<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	Content.loadmodule
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class plgContentLoadmodule extends JPlugin
{
	protected static $modules = array();
	protected static $mods = array();
	/**
	 * Plugin that loads module positions within content
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}

		// simple performance check to determine whether bot should process further
		if (strpos($article->text, 'loadposition') === false && strpos($article->text, 'loadmodule') === false) {
			return true;
		}

		// expression to search for (positions)
		$regex		= '/{loadposition\s+(.*?)}/i';
		$style		= $this->params->def('style', 'none');
		// expression to search for(modules)
		$regexmod	= '/{loadmodule\s+(.*?)}/i';
		$title		= null;
		$stylemod	= $this->params->def('style', 'none');

		// Find all instances of plugin and put in $matches for loadposition
		// $matches[0] is full pattern match, $matches[1] is the position
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);
		// No matches, skip this
		if ($matches) {
			foreach ($matches as $match) {

			$matcheslist = explode(',', $match[1]);

			// We may not have a module style so fall back to the plugin default.
			if (!array_key_exists(1, $matcheslist)) {
				$matcheslist[1] = $style;
			}

			$position = trim($matcheslist[0]);
			$style    = trim($matcheslist[1]);

				$output = $this->_load($position, $style);
				// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
				$article->text = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $article->text, 1);
			}
		}
		// Find all instances of plugin and put in $matchesmod for loadmodule

		preg_match_all($regexmod, $article->text, $matchesmod, PREG_SET_ORDER);
		// If no matches, skip this
		if ($matchesmod){
			foreach ($matchesmod as $matchmod) {

				$matchesmodlist = explode(',', $matchmod[1]);
				//We may not have a specific module so set to null
				if (!array_key_exists(1, $matchesmodlist)) {
					$matchesmodlist[1] = null;
				}
				// We may not have a module style so fall back to the plugin default.
				if (!array_key_exists(2, $matchesmodlist)) {
					$matchesmodlist[2] = $stylemod;
				}

				$module = trim($matchesmodlist[0]);
				$name   = trim($matchesmodlist[1]);
				$style  = trim($matchesmodlist[2]);
				// $match[0] is full pattern match, $match[1] is the module,$match[2] is the title
				$output = $this->_loadmod($module, $name, $style);
				// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
				$article->text = preg_replace("|$matchmod[0]|", addcslashes($output, '\\$'), $article->text, 1);
			}
		}
	}

	protected function _load($position, $style = 'none')
	{
		if (!isset(self::$modules[$position])) {
			self::$modules[$position] = '';
			$document	= JFactory::getDocument();
			$renderer	= $document->loadRenderer('module');
			$modules	= JModuleHelper::getModules($position);
			$params		= array('style' => $style);
			ob_start();

			foreach ($modules as $module) {
				echo $renderer->render($module, $params);
			}

			self::$modules[$position] = ob_get_clean();
		}
		return self::$modules[$position];
	}
	// This is always going to get the first instance of the module type unless
	// there is a title.
	protected function _loadmod($module, $title, $style = 'none')
	{
		if (!isset(self::$mods[$module])) {
			self::$mods[$module] = '';
			$document	= JFactory::getDocument();
			$renderer	= $document->loadRenderer('module');
			$mod		= JModuleHelper::getModule($module, $title);
			// If the module without the mod_ isn't found, try it with mod_.
			// This allows people to enter it either way in the content
			if (!isset($mod)){
				$name = 'mod_'.$module;
				$mod  = JModuleHelper::getModule($name, $title);
			}
			$params = array('style' => $style);
			ob_start();

			echo $renderer->render($mod, $params);

			self::$mods[$module] = ob_get_clean();
		}
		return self::$mods[$module];
	}
}
