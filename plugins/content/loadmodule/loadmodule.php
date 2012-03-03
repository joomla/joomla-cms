<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	Content.loadmodule
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class plgContentLoadmodule extends JPlugin
{
	protected static $positions = array();
	protected static $modules = array();

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 */
	public function onContentPrepare($context, $article, $params, $page = 0)
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
		// expression to search for (modules)
		$regexmod	= '/{loadmodule\s+(.*?)}/i';
		$style		= $this->params->def('style', 'none');


		// Find all instances of plugin and put in $matches for loadposition
		// $matches[0] is full pattern match, $matches[1] is the position
		if (false !== preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				// We may not have a module style so fall back to the plugin default.
				$matcheslist = explode(',',$match[1]) + array(null, $style);
				list($position, $style) = array_map('trim', $matcheslist);

				$output = $this->_load($position, $style);

				// We should replace only first occurrence in order to allow positions
				// with the same name to regenerate their content:
				$article->text = preg_replace("|$match[0]|", addcslashes($output, '\\'), $article->text, 1);
			}
		}

		// Find all instances of plugin and put in $matches for loadmodule
		if (false !== preg_match_all($regexmod, $article->text, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				// We may not have a module style so fall back to the plugin default.
				$matcheslist = explode(',', $match[1]) + array(null, null, $style);
				list($module, $title, $style) = array_map('trim', $matcheslist);
				$output = $this->_loadmod($module, $title, $style);

				// We should replace only first occurrence in order to allow positions
				// with the same name to regenerate their content:
				$article->text = preg_replace("|$match[0]|", addcslashes($output, '\\'), $article->text, 1);
			}
		}
	}

	protected function _load($position, $style = 'none')
	{
		$hash = md5($position . $style);
		
		if (!isset(self::$positions[$hash]))
		{
			self::$positions[$position] = '';
			$renderer	= JFactory::getDocument()->loadRenderer('module');
			$modules	= JModuleHelper::getModules($position);
			$params		= array('style' => $style);
			ob_start();

			foreach ($modules as $module) {
				echo $renderer->render($module, $params);
			}

			self::$positions[$hash] = ob_get_clean();
		}

		return self::$positions[$hash];
	}

	/**
	 * This is always going to get the first instance of the module type
	 * unless there is a title.
	 */
	protected function _loadmod($module, $title, $style = 'none')
	{
		if (substr($module, 0, 4) != 'mod_')
		{
			$module = 'mod_'.$module;
		}
		
		$hash = md5($module . $title . $style);
		
		if (!isset(self::$modules[$hash]))
		{
			self::$modules[$module] = '';
			$renderer	= JFactory::getDocument()->loadRenderer('module');
			$mod		= JModuleHelper::getModule($module, $title);
			$params = array('style' => $style);
			ob_start();

			echo $renderer->render($mod, $params);

			self::$modules[$hash] = ob_get_clean();
		}

		return self::$modules[$hash];
	}
}
