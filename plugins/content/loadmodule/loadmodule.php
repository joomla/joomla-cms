<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgContentLoadmodule extends JPlugin
{
	static $test = 0;

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

		if (self::$test == 1) {
			return;
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
			$article->text = str_replace($match[0], $output, $article->text);
		}

		self::$test = 1;
	}

	protected function _load($position, $style = 'none')
	{
		//if (isset(self::$test[$position]) && self::$test[$position] == 1) return;
		$document	= &JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$modules	= JModuleHelper::getModules($position);
		$params		= array('style' => $style);
		$best = self::$test;
		ob_start();
		foreach ($modules as $module) {
			echo $renderer->render($module, $params);
		}
		$output = ob_get_clean();
		//self::$test[$position] = 1;

		return $output;
	}
}