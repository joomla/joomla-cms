<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for Tabs elements.
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.2
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
	 * @since   11.1
	 */
	public static function start($group = 'tabs', $params = array())
	{
		self::_loadBehavior($group, $params);

		return '<dl class="tabs" id="' . $group . '"><dt style="display:none;"></dt><dd style="display:none;">';
	}

	/**
	 * Close the current pane
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   11.1
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
	 * @since   11.1
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
	 * @since   11.1
	 */
	protected static function _loadBehavior($group, $params = array())
	{
		static $loaded = array();

		if (!array_key_exists((string) $group, $loaded))
		{
			// Include MooTools framework
			JHtml::_('behavior.framework', true);

			$options = '{';
			$opt['onActive'] = (isset($params['onActive'])) ? $params['onActive'] : null;
			$opt['onBackground'] = (isset($params['onBackground'])) ? $params['onBackground'] : null;
			$opt['display'] = (isset($params['startOffset'])) ? (int) $params['startOffset'] : null;
			$opt['useStorage'] = (isset($params['useCookie']) && $params['useCookie']) ? 'true' : 'false';
			$opt['titleSelector'] = "'dt.tabs'";
			$opt['descriptionSelector'] = "'dd.tabs'";

			foreach ($opt as $k => $v)
			{
				if ($v)
				{
					$options .= $k . ': ' . $v . ',';
				}
			}

			if (substr($options, -1) == ',')
			{
				$options = substr($options, 0, -1);
			}

			$options .= '}';

			$js = '	window.addEvent(\'domready\', function(){
						$$(\'dl#' . $group . '.tabs\').each(function(tabs){
							new JTabs(tabs, ' . $options . ');
						});
					});';

			$document = JFactory::getDocument();
			$document->addScriptDeclaration($js);
			JHtml::_('script', 'system/tabs.js', false, true);

			$loaded[(string) $group] = true;
		}
	}
}
