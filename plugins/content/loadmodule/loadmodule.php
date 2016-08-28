<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.loadmodule
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plugin to enable loading modules into content (e.g. articles)
 * This uses the {loadmodule} syntax
 *
 * @since  1.5
 */
class PlgContentLoadmodule extends JPlugin
{
	protected static $modules = array();

	protected static $mods = array();

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed   true if there is an error. Void otherwise.
	 *
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer')
		{
			return true;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, 'loadposition') === false && strpos($article->text, 'loadmodule') === false)
		{
			return true;
		}

		// Get a content parser.
		$parser = new JStringParser;

		// Register the loadposition token.
		// Syntax: {loadposition <module-position>[,<style>]}
		$parser->register(
			'loadposition',
			(new JStringTokenSimple)->callback(
				function(JStringToken $token)
				{
					$tokenParams = $token->getParams();
					$position = trim($tokenParams[0]);
					$style = isset($tokenParams[1]) ? trim($tokenParams[1]) : $this->params->def('style', 'none');

					return addcslashes($this->_load($position, $style), '\\$');
				}
			)
		);

		// Register the loadmodule token.
		// Syntax: {loadmodule <module-type>[,<module-title>[,<style>]]}
		$parser->register(
			'loadmodule',
			(new JStringTokenSimple)->callback(
				function(JStringToken $token)
				{
					$tokenParams = $token->getParams();
					$moduleName = trim($tokenParams[0]);
					$moduleTitle = isset($tokenParams[1]) ? htmlspecialchars_decode(trim($tokenParams[1])) : '';
					$style = isset($tokenParams[2]) ? trim($tokenParams[2]) : $this->params->def('style', 'none');

					return addcslashes($this->_loadmod($moduleName, $moduleTitle, $style), '\\$');
				}
			)
		);

		// Parse and translate the content.
		$article->text = $parser->translate($article->text);
	}

	/**
	 * Loads and renders the module
	 *
	 * @param   string  $position  The position assigned to the module
	 * @param   string  $style     The style assigned to the module
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 */
	protected function _load($position, $style = 'none')
	{
		self::$modules[$position] = '';
		$document = JFactory::getDocument();
		$renderer = $document->loadRenderer('module');
		$modules  = JModuleHelper::getModules($position);
		$params   = array('style' => $style);
		ob_start();

		foreach ($modules as $module)
		{
			echo $renderer->render($module, $params);
		}

		self::$modules[$position] = ob_get_clean();

		return self::$modules[$position];
	}

	/**
	 * This is always going to get the first instance of the module type unless
	 * there is a title.
	 *
	 * @param   string  $module  The module title
	 * @param   string  $title   The title of the module
	 * @param   string  $style   The style of the module
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 */
	protected function _loadmod($module, $title, $style = 'none')
	{
		self::$mods[$module] = '';
		$document = JFactory::getDocument();
		$renderer = $document->loadRenderer('module');
		$mod      = JModuleHelper::getModule($module, $title);

		// If the module without the mod_ isn't found, try it with mod_.
		// This allows people to enter it either way in the content
		if (!isset($mod))
		{
			$name = 'mod_' . $module;
			$mod  = JModuleHelper::getModule($name, $title);
		}

		$params = array('style' => $style);
		ob_start();

		if ($mod->id)
		{
			echo $renderer->render($mod, $params);
		}

		self::$mods[$module] = ob_get_clean();

		return self::$mods[$module];
	}
}
