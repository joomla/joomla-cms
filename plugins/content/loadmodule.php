<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgContentLoadmodule extends JPlugin
{
	/**
	* Plugin that loads module positions within content
	*/
	public function onPrepareContent(&$article, &$params, $page = 0)
	{
		// simple performance check to determine whether bot should process further
		if (strpos($article->text, 'loadposition') === false) {
			return true;
		}

	 	// expression to search for
	 	$regex 		= '/{loadposition\s+(.*?)}/i';
		$matches 	= array();
		$style 		= $this->params->def('style', 'none');

	 	// find all instances of plugin and put in $matches
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			// $match[0] is full pattern match, $match[1] is the position
			$output = $this->_load($match[1], $style);
			$article->text = str_replace($match[0], $output, $article->text);
		}
	}

	protected function _load($position, $style = 'none')
	{
		$document	= &JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$modules 	= JModuleHelper::getModules($position);
		$params		= array('style' => $style);

		ob_start();
		foreach ($modules as $module) {
			echo $renderer->render($module, $params);
		}
		$output = ob_get_clean();
		return $output;
	}
}