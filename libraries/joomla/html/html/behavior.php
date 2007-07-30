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
 * @version		1.5
 */
class JHTMLBehavior
{
	function tooltip($selector='.hasTip', $params = array())
	{
		static $tips;

		if (!isset($tips)) {
			$tips = array();
		}

		// Include mootools framework
		JHTMLBehavior::mootools();

		$sig = md5(serialize(array($selector,$params)));
		if (isset($tips[$sig]) && ($tips[$sig])) {
			return;
		}

		// Setup options object
		$opt['maxTitleChars']	= (isset($params['maxTitleChars']) && ($params['maxTitleChars'])) ? (int)$params['maxTitleChars'] : 50 ;
		$opt['offsets']			= (isset($params['offsets'])) ? (int)$params['offsets'] : null;
		$opt['showDelay']		= (isset($params['showDelay'])) ? (int)$params['showDelay'] : null;
		$opt['hideDelay']		= (isset($params['hideDelay'])) ? (int)$params['hideDelay'] : null;
		$opt['className']		= (isset($params['className'])) ? $params['className'] : null;
		$opt['fixed']			= (isset($params['fixed']) && ($params['fixed'])) ? '\\true' : '\\false';
		$opt['onShow']			= (isset($params['onShow'])) ? '\\'.$params['onShow'] : null;
		$opt['onHide']			= (isset($params['onHide'])) ? '\\'.$params['onHide'] : null;

		$options = JHTMLBehavior::_getJSObject($opt);

		// Attach tooltips to document
		$document =& JFactory::getDocument();
		$tooltipInit = '		window.addEvent(\'domready\', function(){ var JTooltips = new Tips($$(\''.$selector.'\'), '.$options.'); });';
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

			// Include mootools framework
			JHTMLBehavior::mootools();

			// Load the javascript and css
			$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
			$document->addScript($url.'media/system/js/modal.js');
			$document->addStyleSheet($url.'media/system/css/modal.css');

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

	/**
	 * Method to load the mootools framework into the document head
	 *
	 * - If debugging mode is on an uncompressed version of mootools is included for easier debugging.
	 *
	 * @static
	 * @param	boolean	$debug	Is debugging mode on? [optional]
	 * @return	void
	 * @since	1.5
	 */
	function mootools($debug = null)
	{
		global $mainframe;

		static $loaded;

		// Only load once
		if ($loaded) {
			return;
		}

		// If no debugging value is set, use the configuration setting
		if ($debug === null) {
			$config = &JFactory::getConfig();
			$debug = $config->getValue('config.debug');
		}

		// TODO NOTE: Here we are checking for Konqueror - If they fix thier issue with compressed, we will need to update this
		$konkcheck = strpos (strtolower($_SERVER['HTTP_USER_AGENT']), "konqueror");

		// Get the document object and base URL
		$document =& JFactory::getDocument();
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		if ($debug || $konkcheck) {
			$document->addScript($url.'media/system/js/mootools-uncompressed.js');
		} else {
			$document->addScript($url.'media/system/js/mootools.js');
		}
		$loaded = true;
		return;
	}

	function caption()
	{
		global $mainframe;

		// Include mootools framework
		JHTMLBehavior::mootools();

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$doc = &JFactory::getDocument();
		$doc->addScript( $url. 'media/system/js/caption.js' );
	}

	function formvalidation()
	{
		global $mainframe;

		// Include mootools framework
		JHTMLBehavior::mootools();

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$doc = &JFactory::getDocument();
		$doc->addScript( $url. 'media/system/js/validate.js' );
	}

	function switcher()
	{
		global $mainframe;

		// Include mootools framework
		JHTMLBehavior::mootools();

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$doc = &JFactory::getDocument();
		$doc->addScript( $url. 'media/system/js/switcher.js' );
	}

	function combobox()
	{
		global $mainframe;

		// Include mootools framework
		JHTMLBehavior::mootools();

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$doc = &JFactory::getDocument();
		$doc->addScript( $url. 'media/system/js/combobox.js' );
	}

	function uploader($id='file-upload', $params = array())
	{
		global $mainframe;

		// Include mootools framework
		JHTMLBehavior::mootools();

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$document = &JFactory::getDocument();
		$document->addScript( $url. 'media/system/js/swf.js' );
		$document->addScript( $url. 'media/system/js/uploader.js' );

		static $uploaders;

		if (!isset($uploaders)) {
			$uploaders = array();
		}

		if (isset($uploaders[$id]) && ($uploaders[$id])) {
			return;
		}

		// Setup options object
		$opt['url']					= (isset($params['targetURL'])) ? $params['targetURL'] : null ;
		$opt['swf']					= (isset($params['swf'])) ? $params['swf'] : $url.'media/system/swf/uploader.swf';
		$opt['multiple']			= (isset($params['multiple']) && !($params['multiple'])) ? '\\false' : '\\true';
		$opt['queued']				= (isset($params['queued']) && !($params['queued'])) ? '\\false' : '\\true';
		$opt['queueList']			= (isset($params['queueList'])) ? $params['queueList'] : 'upload-queue';
		$opt['instantStart']		= (isset($params['instantStart']) && ($params['instantStart'])) ? '\\true' : '\\false';
		$opt['allowDuplicates']		= (isset($params['allowDuplicates']) && !($params['allowDuplicates'])) ? '\\false' : '\\true';
		$opt['limitSize']			= (isset($params['limitSize']) && ($params['limitSize'])) ? (int)$params['limitSize'] : null;
		$opt['limitFiles']			= (isset($params['limitFiles']) && ($params['limitFiles'])) ? (int)$params['limitFiles'] : null;
		$opt['optionFxDuration']	= (isset($params['optionFxDuration'])) ? (int)$params['optionFxDuration'] : null;
		$opt['container']			= (isset($params['container'])) ? '\\$('.$params['container'].')' : null;
		$opt['types']				= (isset($params['types'])) ?'\\'.$params['types'] : '\\{\'All Files (*.*)\': \'*.*\'}';


		// Optional functions
		$opt['createReplacement']	= (isset($params['createReplacement'])) ? '\\'.$params['createReplacement'] : null;
		$opt['onComplete']			= (isset($params['onComplete'])) ? '\\'.$params['onComplete'] : null;
		$opt['onAllComplete']		= (isset($params['onAllComplete'])) ? '\\'.$params['onAllComplete'] : null;

/*  types: Object with (description: extension) pairs, default: Images (*.jpg; *.jpeg; *.gif; *.png)
 */

		$options = JHTMLBehavior::_getJSObject($opt);

		// Attach tooltips to document
		$document =& JFactory::getDocument();
		$uploaderInit = '		window.addEvent(\'load\', function(){
				var Uploader = new FancyUpload($(\''.$id.'\'), '.$options.');
				$(\'upload-clear\').adopt(new Element(\'input\', { type: \'button\', events: { click: Uploader.clearList.bind(Uploader, [false])}, value: \''.JText::_('Clear Completed').'\' }));				});';
		$document->addScriptDeclaration($uploaderInit);

		// Set static array
		$uploaders[$id] = true;
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
		// Include mootools framework
		JHTMLBehavior::mootools();

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
			setTimeout('keepAlive(' + refreshTime + ')', refreshTime);
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
				$object .= ' '.$k.': ';
				$object .= (is_numeric($v) || strpos($v, '\\') === 0) ? (is_numeric($v)) ? $v : substr($v, 1) : "'".$v."'";
				$object .= ',';
			} else {
				$object .= ' '.$k.': '.JHTMLBehavior::_getJSObject($v).',';
			}
		}
		if (substr($object, -1) == ',') {
			$object = substr($object, 0, -1);
		}
		$object .= '}';

		return $object;
	}
}

