<?php
/**
* @version		$Id$
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
	function tooltip($selector='.hasTip', $params = array())
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
		$opt['maxTitleChars']	= (isset($params['maxTitleChars']) && ($params['maxTitleChars'])) ? (int)$params['maxTitleChars'] : 50 ;
		$opt['offsets']			= (isset($params['offsets'])) ? (int)$params['offsets'] : null;
		$opt['showDelay']		= (isset($params['showDelay'])) ? (int)$params['showDelay'] : null;
		$opt['hideDelay']		= (isset($params['hideDelay'])) ? (int)$params['hideDelay'] : null;
		$opt['className']		= (isset($params['className'])) ? "'".$params['className']."'" : null;
		$opt['fixed']			= (isset($params['fixed']) && ($params['fixed'])) ? 'true' : 'false';
		$opt['onShow']			= (isset($params['onShow'])) ? $params['onShow'] : null;
		$opt['onHide']			= (isset($params['onHide'])) ? $params['onHide'] : null;

		$options = JHTMLBehavior::_getJSObject($opt);

		// Attach tooltips to document
		$document =& JFactory::getDocument();
		$tooltipInit = '		Window.onDomReady(function(){ var JTooltips = new Tips($$(\''.$selector.'\'), '.$options.'); });';
		$document->addScriptDeclaration($tooltipInit);

		// Set static array
		$tips[$sig] = true;
		return;
	}

	function modal($selector='a.modal', $params = array())
	{
		global $mainframe;

		static $modals;
		static $included;

		$document =& JFactory::getDocument();

		// Load the necessary files if they haven't yet been loaded
		if (!isset($included)) {

			$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
			$document->addScript($url.'includes/js/modal/modal.js');
			$document->addStyleSheet($url.'includes/js/modal/modal.css');

			$included = true;
		}

		if (!isset($modals)) {
			$modals = array();
		}

		$sig = md5(serialize(array($selector,$params)));
		if (isset($modals[$sig]) && ($modals[$sig])) {
			return;
		}

		// Setup options object
		$opt['ajaxOptions']	= (isset($params['ajaxOptions']) && (is_array($params['ajaxOptions']))) ? $params['ajaxOptions'] : null;
		$opt['size']		= (isset($params['size']) && (is_array($params['size']))) ? $params['size'] : null;
		$opt['onOpen']		= (isset($params['onOpen'])) ? $params['onOpen'] : null;
		$opt['onClose']		= (isset($params['onClose'])) ? $params['onClose'] : null;
		$opt['onUpdate']	= (isset($params['onUpdate'])) ? $params['onUpdate'] : null;
		$opt['onResize']	= (isset($params['onResize'])) ? $params['onResize'] : null;
		$opt['onMove']		= (isset($params['onMove'])) ? $params['onMove'] : null;
		$opt['onShow']		= (isset($params['onShow'])) ? $params['onShow'] : null;
		$opt['onHide']		= (isset($params['onHide'])) ? $params['onHide'] : null;

		$options = JHTMLBehavior::_getJSObject($opt);

		// Attach modal behavior to document
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {

			SqueezeBox.initialize(".$options.");

			$$('".$selector."').each(function(el) {
				el.addEvent('click', function(e) {
					new Event(e).stop();
					SqueezeBox.fromElement(el);
				});
			});
		});");

		// Set static array
		$modals[$sig] = true;
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
		$config 		=& JFactory::getConfig();
		$lifetime 	= ( $config->getValue('lifetime') * 60000 );
		$refreshTime =  ( $lifetime < 120000 ) ? 120000 : $lifetime - 120000;
		//refresh time is 2 minutes less than the liftime assined in the configuration.php file
		?>
		<script language="javascript">
		function keepAlive( refreshTime )
		{
			var url = "index.php?option=com_admin&tmpl=component&task=keepalive";
			var myAjax = new Ajax( url, { method: "get", update: $("keepAliveLayer") } ).request();
			setTimeout('keepAlive()', refreshTime );
		}

		window.addEvent('domready', function()
			{ keepAlive( <?php echo $refreshTime; ?> ); }
		);
		</script>
		<div id="keepAliveLayer"></div>
		<?php
		return;
	}


	/**
	 * Internal method to get a JavaScript object notation string from an array
	 *
	 * @param	array	$array	The array to convert to JavaScript object notation
	 * @return	string	JavaScript object notation representation of the array
	 * @since	1.5
	 */
	function _getJSObject($array=array())
	{
		// Initialize variables
		$object = '{';

		// Iterate over array to build objects
		foreach ((array)$array as $k => $v)
		{
			if (is_null($v)) {
				continue;
			}
			if (!is_array($v) && !is_object($v)) {
				$object .= $k.': ';
				$object .= (is_numeric($v)) ? $v : "'".$v."'";
				$object .= ',';
			} else {
				$object .= $k.': '.JHTMLBehavior::_getJSObject($v).',';
			}
		}
		if (substr($object, -1) == ',') {
			$object = substr($object, 0, -1);
		}
		$object .= '}';

		return $object;
	}
}

