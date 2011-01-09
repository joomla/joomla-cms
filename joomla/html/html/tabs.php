<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Utility class for Tabs elements.
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @version		1.6
 */
abstract class JHtmlTabs
{
	protected static $opened = array();

	/**
	 * Creates a panes and creates the JavaScript object for it.
	 *
	 * @param	string	The pane identifier
	 * @param	array	An array of option.
	 * @return	string
	 * @since	1.6
	 */
	public static function start($group='tabs', $params=array())
	{
		JHtmlTabs::_loadBehavior($group,$params);
		array_push(JHtmlTabs::$opened,false);

		return '<dl class="tabs" id="'.$group.'">';
	}

	/**
	 * Close the current pane
	 *
	 * @return	string
	 * @since	1.6
	 */
	public static function end()
	{
		if (array_pop(JHtmlTabs::$opened))
		{
			$close = '</dd>';
		}
		else
		{
			$close = '';
		}

		return $close.'</dl>';
	}

	/**
	 * Begins the display of a new panel.
	 *
	 * @param	string	Text to display.
	 * @param	string	Identifier of the panel.
	 * @return	string
	 * @since	1.6
	 */
	public static function panel($text, $id)
	{
		if (JHtmlTabs::$opened[count(JHtmlTabs::$opened)-1])
		{
			$close = '</dd>';
		}
		else
		{
			JHtmlTabs::$opened[count(JHtmlTabs::$opened)-1] = true;
			$close = '';
		}

		return $close.'<dt class="'.$id.'"><span><h3><a href="javascript:void(0);">'.$text.'</a></h3></span></dt><dd>';
	}

	/**
	 * Load the JavaScript behavior.
	 *
	 * @param	string	The pane identifier.
	 * @param	array	Array of options.
	 * @return	void
	 * @since	1.6
	 */
	protected static function _loadBehavior($group, $params = array())
	{
		static $loaded = array();

		if (!array_key_exists($group,$loaded))
		{
			// Include mootools framework
			JHtml::_('behavior.framework', true);

			$display = (isset($params['startOffset'])) ? (int)$params['startOffset'] : null ;
			$options = '{';
			$opt['onActive']		= (isset($params['onActive'])) ? $params['onActive'] : null ;
			$opt['onBackground']	= (isset($params['onBackground'])) ? $params['onBackground'] : null ;
			$opt['display']			= (isset($params['useCookie']) && $params['useCookie']) ? JRequest::getInt('jpanetabs_' . $group, $display, 'cookie') : $display ;
			foreach ($opt as $k => $v)
			{
				if ($v) {
					$options .= $k.': '.$v.',';
				}
			}
			if (substr($options, -1) == ',') {
				$options = substr($options, 0, -1);
			}
			$options .= '}';

			$js = '	window.addEvent(\'domready\', function(){ $$(\'dl#'.$group.'.tabs\').each(function(tabs){ new JTabs(tabs, '.$options.'); }); });';

			$document = JFactory::getDocument();
			$document->addScriptDeclaration($js);
			JHTML::_('script','system/tabs.js', false, true);

			$loaded[$group] = true;
		}
	}
}
