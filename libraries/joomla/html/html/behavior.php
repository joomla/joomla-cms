<?php
/**
* @version		$Id: select.php 7116 2007-04-09 22:40:27Z eddieajau $
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Utility class for javascript behaviors
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTMLBehavior
{
	function tooltip($selector='.hasTip', $params=array())
	{
		static $tips;

		if (!isset($tips)) {
			$tips = array();
		}

		$sig = md5(serialize(array($selector,$params)));
		if (isset($tips[$sig]) && ($tips[$sig])) {
			return;
		}

		// Setup options object
		$options = '{';
		$opt['maxTitleChars']	= (isset($params['maxTitleChars']) && ($params['maxTitleChars'])) ? (int)$params['maxTitleChars'] : 50 ;
		$opt['timeOut']			= (isset($params['timeOut'])) ? (int)$params['timeOut'] : null;
		$opt['showDelay']		= (isset($params['showDelay'])) ? (int)$params['showDelay'] : null;
		$opt['hideDelay']		= (isset($params['hideDelay'])) ? (int)$params['hideDelay'] : null;
		$opt['className']		= (isset($params['className'])) ? $params['className'] : null;
		$opt['fixed']			= (isset($params['fixed']) && ($params['fixed'])) ? 'true' : 'false';
		$opt['onShow']			= (isset($params['onShow'])) ? $params['onShow'] : null;
		$opt['onHide']			= (isset($params['onHide'])) ? $params['onHide'] : null;
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

		// Attach tooltips to document
		$document =& JFactory::getDocument();
		$tooltipInit = '		Window.onDomReady(function(){ var JTooltips = new Tips($$(\''.$selector.'\'), '.$options.'); });';
		$document->addScriptDeclaration($tooltipInit);

		// Set static array
		$tips[$sig] = true;
		return;
	}
	
	function calendar()
	{
		global $mainframe;

		$doc =& JFactory::getDocument();
		$lang =& JFactory::getLanguage();
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$doc->addStyleSheet( $url. 'includes/js/calendar/calendar-mos.css', 'text/css', null, array(' title' => JText::_( 'green' ) ,' media' => 'all' ));
		$doc->addScript( $url. 'includes/js/calendar/calendar_mini.js' );
		$langScript = JPATH_SITE.DS.'includes'.DS.'js'.DS.'calendar'.DS.'lang'.DS.'calendar-'.$lang->getTag().'.js';
		if( file_exists( $langScript ) ){
			$doc->addScript( $url. 'includes/js/calendar/lang/calendar-'.$lang->getTag().'.js' );
		} else {
			$doc->addScript( $url. 'includes/js/calendar/lang/calendar-en-GB.js' );
		}
	}
	
	/**
	 * Keep session alive, for example, while editing or creating an article.
	 */
	function keepalive()
	{
		$js = "
				function keepAlive() {
					setTimeout('frames[\'keepAliveFrame\'].location.href=\'index.php?option=com_admin&tmpl=component&task=keepalive\';', 60000);
				}";

		$html = "<iframe id=\"keepAliveFrame\" name=\"keepAliveFrame\" " .
				"style=\"width:0px; height:0px; border: 0px\" " .
				"src=\"index.php?option=com_admin&tmpl=component&task=keepalive\" " .
				"onload=\"keepAlive();\"></iframe>";

		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		echo $html;
	}
}

