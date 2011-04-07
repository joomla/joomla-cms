<?php
/**
 * @version		$Id$
 * @package		Joomla.Plugin
 * @subpackage	Content.loadmodule
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgContentLoadmodule extends JPlugin
{
	protected static $modules=array();
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
		// simple performance check to determine whether bot should process further
		if (strpos($article->text, 'loadposition') === false) {
			return true;
		}
		
		// expression to search for
		$regex		= '/{loadposition\s+(.*?)}/i';
		$matches	= array();
		$style		= $this->params->def('style', 'none');

		// find all instances of plugin and put in $matches
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			// $match[0] is full pattern match, $match[1] is the position
			$output = $this->_load($match[1], $style);
			// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
			$article->text = preg_replace("|$match[0]|", $output, $article->text, 1);
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
}
