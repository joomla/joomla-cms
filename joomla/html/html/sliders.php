<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Utility class for Sliders elements
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.6
 */
abstract class JHtmlSliders
{
	protected static $opened = array();

	/**
	 * Creates a panes and loads the javascript behavior for it.
	 *
	 * @param	string	The pane identifier.
	 * @param	array	An array of options.
	 * @return	string
	 * @since	1.6
	 */
	public static function start($group = 'sliders', $params = array())
	{
		JHtmlSliders::_loadBehavior($group,$params);
		array_push(JHtmlSliders::$opened,false);

		return '<div id="'.$group.'" class="pane-sliders">';
	}

	/**
	 * Close the current pane.
	 *
	 * @return	string
	 * @since	1.6
	 */
	public static function end()
	{
		if (array_pop(JHtmlSliders::$opened))
		{
			$close = '</div></div>';
		}
		else
		{
			$close = '';
		}

		return $close.'</div>';
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
		if (JHtmlSliders::$opened[count(JHtmlSliders::$opened)-1])
		{
			$close = '</div></div>';
		}
		else
		{
			JHtmlSliders::$opened[count(JHtmlSliders::$opened)-1] = true;
			$close = '';
		}

		return $close.'<div class="panel"><h3 class="jpane-toggler title" id="'.$id.'"><a href="#"><span>'.$text.'</span></a></h3><div class="jpane-slider content">';
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
		static $loaded=array();
		if (!array_key_exists($group,$loaded))
		{
			$loaded[$group] = true;
			// Include mootools framework.
			JHtml::_('behavior.framework', true);

			$document = &JFactory::getDocument();

			$display = (isset($params['startOffset']) && ($params['startTransition'])) ? (int)$params['startOffset'] : null;
			$show = (isset($params['startOffset']) && (!$params['startTransition'])) ? (int)$params['startOffset'] : null;
			$options = '{';
			$opt['onActive']	 = 'function(toggler, i) {toggler.addClass(\'jpane-toggler-down\');toggler.removeClass(\'jpane-toggler\');Cookie.write(\'jpanesliders_'.$group.'\',$$(\'div#'.$group.'.pane-sliders .panel h3\').indexOf(toggler));}';
			$opt['onBackground']	 = "function(toggler, i) {toggler.addClass('jpane-toggler');toggler.removeClass('jpane-toggler-down');if($$('div#".$group.".pane-sliders .panel h3').length==$$('div#".$group.".pane-sliders .panel h3.jpane-toggler').length) Cookie.write('jpanesliders_".$group."',-1);}";
			$opt['duration']	 = (isset($params['duration'])) ? (int)$params['duration'] : 300;
			$opt['display']		 = (isset($params['useCookie']) && $params['useCookie']) ? JRequest::getInt('jpanesliders_' . $group, $display, 'cookie') : $display ;
			$opt['show']		 = (isset($params['useCookie']) && $params['useCookie']) ? JRequest::getInt('jpanesliders_' . $group, $show, 'cookie') : $show ;
			$opt['opacity']		 = (isset($params['opacityTransition']) && ($params['opacityTransition'])) ? 'true' : 'false' ;
			$opt['alwaysHide']	 = (isset($params['allowAllClose']) && (!$params['allowAllClose'])) ? 'false' : 'true';
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

			$js = '	window.addEvent(\'domready\', function(){ new Accordion($$(\'div#'.$group.'.pane-sliders .panel h3.jpane-toggler\'), $$(\'div#'.$group.'.pane-sliders .panel div.jpane-slider\'), '.$options.'); });';

			$document->addScriptDeclaration($js);
		}
	}
}
