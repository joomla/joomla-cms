<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for Tabs elements.
 *
 * @since       1.6
 * @deprecated  __DEPLOY_VERSION__ These helpers are dependent on the deprecated MooTools support
 */
abstract class JHtmlTabs
{
	/**
	 * Creates a panes and creates the JavaScript object for it.
	 *
	 * @param   string  $group   The pane identifier.
	 * @param   array   $params  An array of option.
	 *
	 * @return  string
	 *
	 * @since   1.6
	 * @deprecated  __DEPLOY_VERSION__ These helpers are dependent on the deprecated MooTools support
	 */
	public static function start($group = 'tabs', $params = array())
	{
		static::loadBehavior($group, $params);

		return '<dl class="tabs" id="' . $group . '"><dt style="display:none;"></dt><dd style="display:none;">';
	}

	/**
	 * Close the current pane
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   1.6
	 * @deprecated  __DEPLOY_VERSION__ These helpers are dependent on the deprecated MooTools support
	 */
	public static function end()
	{
		return '</dd></dl>';
	}

	/**
	 * Begins the display of a new panel.
	 *
	 * @param   string  $text  Text to display.
	 * @param   string  $id    Identifier of the panel.
	 *
	 * @return  string  HTML to start a new panel
	 *
	 * @since   1.6
	 * @deprecated  __DEPLOY_VERSION__ These helpers are dependent on the deprecated MooTools support
	 */
	public static function panel($text, $id)
	{
		return '</dd><dt class="tabs ' . $id . '"><span><h3><a href="javascript:void(0);">' . $text . '</a></h3></span></dt><dd class="tabs">';
	}

	/**
	 * Load the JavaScript behavior.
	 *
	 * @param   string  $group   The pane identifier.
	 * @param   array   $params  Array of options.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @deprecated  __DEPLOY_VERSION__ These helpers are dependent on the deprecated MooTools support
	 */
	protected static function loadBehavior($group, $params = array())
	{
		static $loaded = array();

		if (!array_key_exists((string) $group, $loaded))
		{
			// Include MooTools framework
			JHtml::_('behavior.framework', true);

			$opt['onActive']            = (isset($params['onActive'])) ? '\\' . $params['onActive'] : null;
			$opt['onBackground']        = (isset($params['onBackground'])) ? '\\' . $params['onBackground'] : null;
			$opt['display']             = (isset($params['startOffset'])) ? (int) $params['startOffset'] : null;
			$opt['titleSelector']       = 'dt.tabs';
			$opt['descriptionSelector'] = 'dd.tabs';

			// When use storage is set and value is false - By default we allow to use storage
			$opt['useStorage'] = (isset($params['useCookie']) && !$params['useCookie']) ? false : true;

			$options = JHtml::getJSObject($opt);

			$js = '	window.addEvent(\'domready\', function(){
						$$(\'dl#' . $group . '.tabs\').each(function(tabs){
							new JTabs(tabs, ' . $options . ');
						});
					});';

			$document = JFactory::getDocument();
			$document->addScriptDeclaration($js);

			JHtml::_('script', 'system/tabs.min.js', array('version' => 'auto', 'relative' => true));

			$loaded[(string) $group] = true;
		}
	}
}
