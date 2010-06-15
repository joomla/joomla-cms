<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Utility class for javascript behaviors
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @version		1.5
 */
abstract class JHtmlBehavior
{
	/**
	 * Method to load the mootools framework into the document head
	 *
	 * - If debugging mode is on an uncompressed version of mootools is included for easier debugging.
	 *
	 * @static
	 * @param	string	$type	Mootools file to load
	 * @param	boolean	$debug	Is debugging mode on? [optional]
	 * @return	void
	 * @since	1.6
	 */
	public static function framework($extras = false, $debug = null)
	{
		static $loaded = array();

		$type = $extras ? 'more' : 'core';

		// Only load once
		if (!empty($loaded[$type])) {
			return;
		}

		JHtml::core($debug);

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = &JFactory::getConfig();
			$debug = $config->get('debug');
		}

		// TODO NOTE: Here we are checking for Konqueror - If they fix thier issue with compressed, we will need to update this
		$konkcheck		= isset($_SERVER['HTTP_USER_AGENT']) ? strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'konqueror') : null;
		$uncompressed	= ($debug || $konkcheck) ? '-uncompressed' : '';

		if ($type != 'core' && empty($loaded['core'])) {
			self::framework(false);
		}

		JHtml::_('script','system/mootools-'.$type.$uncompressed.'.js', false, true, false, false);
		$loaded[$type] = true;
		return;
	}

	/**
	 * Deprecated. Use JHtmlBehavior::framework() instead.
	 *
	 * @static
	 * @param	boolean	$debug	Is debugging mode on? [optional]
	 * @return	void
	 * @since	1.5
	 */
	public static function mootools($debug = null)
	{
		self::framework(true, $debug);
	}

	public static function caption()
	{
		JHtml::_('script','system/caption.js', false, true);
	}

	public static function formvalidation()
	{
		JHtml::_('script','system/validate.js', false, true);
	}

	public static function switcher()
	{
		JHtml::_('behavior.framework');
		JHtml::_('script','system/switcher.js', false, true);

		$script = "
			document.switcher = null;
			window.addEvent('domready', function(){
				toggler = document.id('submenu');
				element = document.id('config-document');
				if(element) {
					document.switcher = new JSwitcher(toggler, element, {cookieName: toggler.getAttribute('class')});
				}
			});";

		JFactory::getDocument()->addScriptDeclaration($script);
	}

	public static function combobox()
	{
		JHtml::_('script','system/combobox.js', false, true);
	}

	public static function tooltip($selector='.hasTip', $params = array())
	{
		static $tips;

		if (!isset($tips)) {
			$tips = array();
		}

		// Include mootools framework
		JHtml::_('behavior.framework', true);

		$sig = md5(serialize(array($selector,$params)));
		if (isset($tips[$sig]) && ($tips[$sig])) {
			return;
		}

		// Setup options object
		$opt['maxTitleChars']	= (isset($params['maxTitleChars']) && ($params['maxTitleChars'])) ? (int)$params['maxTitleChars'] : 50 ;
		// offsets needs an array in the format: array('x'=>20, 'y'=>30)
		$opt['offsets']			= (isset($params['offsets']) && (is_array($params['offsets']))) ? $params['offsets'] : null;
		$opt['showDelay']		= (isset($params['showDelay'])) ? (int)$params['showDelay'] : null;
		$opt['hideDelay']		= (isset($params['hideDelay'])) ? (int)$params['hideDelay'] : null;
		$opt['className']		= (isset($params['className'])) ? $params['className'] : null;
		$opt['fixed']			= (isset($params['fixed']) && ($params['fixed'])) ? '\\true' : '\\false';
		$opt['onShow']			= (isset($params['onShow'])) ? '\\'.$params['onShow'] : null;
		$opt['onHide']			= (isset($params['onHide'])) ? '\\'.$params['onHide'] : null;

		$options = JHtmlBehavior::_getJSObject($opt);

		// Attach tooltips to document
		$document = &JFactory::getDocument();
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			$$('$selector').each(function(el) {
				var title = el.get('title');
				if (title) {
					var parts = title.split('::', 2);
					el.store('tip:title', parts[0]);
					el.store('tip:text', parts[1]);
				}
			});
			var JTooltips = new Tips($$('$selector'), $options);
		});");

		// Set static array
		$tips[$sig] = true;
		return;
	}

	public static function modal($selector='a.modal', $params = array())
	{
		static $modals;
		static $included;

		$document = &JFactory::getDocument();

		// Load the necessary files if they haven't yet been loaded
		if (!isset($included)) {
			// Load the javascript and css
			JHtml::_('behavior.framework');
			JHtml::_('script','system/modal.js', false, true);
			JHtml::_('stylesheet','system/modal.css', array(), true);

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
		$opt['shadow']		= (isset($params['shadow'])) ? $params['shadow'] : null;
		$opt['onOpen']		= (isset($params['onOpen'])) ? $params['onOpen'] : null;
		$opt['onClose']		= (isset($params['onClose'])) ? $params['onClose'] : null;
		$opt['onUpdate']	= (isset($params['onUpdate'])) ? $params['onUpdate'] : null;
		$opt['onResize']	= (isset($params['onResize'])) ? $params['onResize'] : null;
		$opt['onMove']		= (isset($params['onMove'])) ? $params['onMove'] : null;
		$opt['onShow']		= (isset($params['onShow'])) ? $params['onShow'] : null;
		$opt['onHide']		= (isset($params['onHide'])) ? $params['onHide'] : null;

		$options = JHtmlBehavior::_getJSObject($opt);

		// Attach modal behavior to document
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {

			SqueezeBox.initialize(".$options.");
			SqueezeBox.assign($$('".$selector."'), {
				parse: 'rel'
			});
		});");

		// Set static array
		$modals[$sig] = true;
		return;
	}

	public static function uploader($id='file-upload', $params = array(), $upload_queue='upload-queue')
	{
		JHtml::_('script','system/swf.js', false, true);
		JHtml::_('script','system/progressbar.js', false, true);
		JHtml::_('script','system/uploader.js', false, true);

		$document = &JFactory::getDocument();

		static $uploaders;

		if (!isset($uploaders)) {
			$uploaders = array();

			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_FILENAME');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_UPLOAD_COMPLETED');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_ERROR_OCCURRED');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_ALL_FILES');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_PROGRESS_OVERALL');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_REMOVE');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_REMOVE_TITLE');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_FILE');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_PROGRESS');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_FILE_ERROR');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_FILE_SUCCESSFULLY_UPLOADED');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_VALIDATION_ERROR_DUPLICATE');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_VALIDATION_ERROR_SIZELIMITMIN');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_VALIDATION_ERROR_SIZELIMITMAX');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_VALIDATION_ERROR_FILELISTMAX');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_VALIDATION_ERROR_FILELISTSIZEMAX');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_ERROR_HTTPSTATUS');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_ERROR_SECURITYERROR');
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_ERROR_IOERROR');
		}


		if (isset($uploaders[$id]) && ($uploaders[$id])) {
			return;
		}

		$onFileSuccess = '\\function(file, response) {
			var json = new Hash(JSON.decode(response, true) || {});

			if (json.get(\'status\') == \'1\') {
				file.element.addClass(\'file-success\');
				file.info.set(\'html\', \'<strong>\' + Joomla.JText._(\'JLIB_HTML_BEHAVIOR_UPLOADER_FILE_SUCCESSFULLY_UPLOADED\') + \'</strong>\');
			} else {
				file.element.addClass(\'file-failed\');
				file.info.set(\'html\', \'<strong>\' +
					Joomla.JText._(\'JLIB_HTML_BEHAVIOR_UPLOADER_ERROR_OCCURRED\', \'An Error Occurred\').substitute({ error: json.get(\'error\') }) + \'</strong>\');
			}
		}';



		// Setup options object
		$opt['verbose']				= true;
		$opt['url']					= (isset($params['targetURL'])) ? $params['targetURL'] : null ;
		$opt['path']				= (isset($params['swf'])) ? $params['swf'] : JURI::root(true).'/media/system/swf/uploader.swf';
		$opt['height']				= (isset($params['height'])) && $params['height'] ? (int)$params['height'] : null;
		$opt['width']				= (isset($params['width'])) && $params['width'] ? (int)$params['width'] : null;
		$opt['multiple']			= (isset($params['multiple']) && !($params['multiple'])) ? '\\false' : '\\true';
		$opt['queued']				= (isset($params['queued']) && !($params['queued'])) ? (int)$params['queued'] : null;
		$opt['target']				= (isset($params['target'])) ? $params['target'] : '\\$(\'upload-browse\')';
		$opt['instantStart']		= (isset($params['instantStart']) && ($params['instantStart'])) ? '\\true' : '\\false';
		$opt['allowDuplicates']		= (isset($params['allowDuplicates']) && !($params['allowDuplicates'])) ? '\\false' : '\\true';
		// limitSize is the old parameter name.  Remove in 1.7
		$opt['fileSizeMax']			= (isset($params['limitSize']) && ($params['limitSize'])) ? (int)$params['limitSize'] : null;
		// fileSizeMax is the new name.  If supplied, it will override the old value specified for limitSize
		$opt['fileSizeMax']			= (isset($params['fileSizeMax']) && ($params['fileSizeMax'])) ? (int)$params['fileSizeMax'] : $opt['fileSizeMax'];
		$opt['fileSizeMin']			= (isset($params['fileSizeMin']) && ($params['fileSizeMin'])) ? (int)$params['fileSizeMin'] : null;
		// limitFiles is the old parameter name.  Remove in 1.7
		$opt['fileListMax']			= (isset($params['limitFiles']) && ($params['limitFiles'])) ? (int)$params['limitFiles'] : null;
		// fileListMax is the new name.  If supplied, it will override the old value specified for limitFiles
		$opt['fileListMax']			= (isset($params['fileListMax']) && ($params['fileListMax'])) ? (int)$params['fileListMax'] : $opt['fileListMax'];
		$opt['fileListSizeMax']		= (isset($params['fileListSizeMax']) && ($params['fileListSizeMax'])) ? (int)$params['fileListSizeMax'] : null;
		// types is the old parameter name.  Remove in 1.7
		$opt['typeFilter']			= (isset($params['types'])) ? '\\'.$params['types'] : '\\{Joomla.JText._(\'JPLOADER_ALL_FILES\'): \'*.*\'}';
		$opt['typeFilter']			= (isset($params['typeFilter'])) ? '\\'.$params['typeFilter'] : $opt['typeFilter'];


		// Optional functions
		$opt['createReplacement'] 	= (isset($params['createReplacement'])) ? '\\'.$params['createReplacement'] : null;
		$opt['onFileComplete'] 		= (isset($params['onFileComplete'])) ? '\\'.$params['onFileComplete'] : null;
		$opt['onBeforeStart'] 		= (isset($params['onBeforeStart'])) ? '\\'.$params['onBeforeStart'] : null;
		$opt['onStart'] 			= (isset($params['onStart'])) ? '\\'.$params['onStart'] : null;
		$opt['onComplete'] 			= (isset($params['onComplete'])) ? '\\'.$params['onComplete'] : null;
		$opt['onFileSuccess'] 		= (isset($params['onFileSuccess'])) ? '\\'.$params['onFileSuccess'] : $onFileSuccess;

		if(!isset($params['startButton'])) $params['startButton'] = 'upload-start';
		if(!isset($params['clearButton'])) $params['clearButton'] = 'upload-clear';

		$opt['onLoad'] =
			'\\function() {
				document.id(\''.$id.'\').removeClass(\'hide\'); // we show the actual UI
				document.id(\'upload-noflash\').destroy(); // ... and hide the plain form

				// We relay the interactions with the overlayed flash to the link
				this.target.addEvents({
					click: function() {
						return false;
					},
					mouseenter: function() {
						this.addClass(\'hover\');
					},
					mouseleave: function() {
						this.removeClass(\'hover\');
						this.blur();
					},
					mousedown: function() {
						this.focus();
					}
				});

				// Interactions for the 2 other buttons

				document.id(\''.$params['clearButton'].'\').addEvent(\'click\', function() {
					Uploader.remove(); // remove all files
					return false;
				});

				document.id(\''.$params['startButton'].'\').addEvent(\'click\', function() {
					Uploader.start(); // start upload
					return false;
				});
			}';

		$options = JHtmlBehavior::_getJSObject($opt);

		// Attach tooltips to document
		$uploaderInit =
				'window.addEvent(\'domready\', function(){
				var Uploader = new FancyUpload2($(\''.$id.'\'), $(\''.$upload_queue.'\'), '.$options.' );
				});';
		$document->addScriptDeclaration($uploaderInit);

		// Set static array
		$uploaders[$id] = true;
		return;
	}

	public static function tree($id, $params = array(), $root = array())
	{
		static $trees;

		if (!isset($trees)) {
			$trees = array();
		}

		// Include mootools framework
		JHtml::_('behavior.framework');
		JHtml::_('script','system/mootree.js', false, true, false, false);
		JHtml::_('stylesheet','system/mootree.css', array(), true);

		if (isset($trees[$id]) && ($trees[$id])) {
			return;
		}

		// Setup options object
		$opt['div']		= (array_key_exists('div', $params)) ? $params['div'] : $id.'_tree';
		$opt['mode']	= (array_key_exists('mode', $params)) ? $params['mode'] : 'folders';
		$opt['grid']	= (array_key_exists('grid', $params)) ? '\\'.$params['grid'] : '\\true';
		$opt['theme']	= (array_key_exists('theme', $params)) ? $params['theme'] : JHtml::_('image','system/mootree.gif', '', array(), true, true);

		// Event handlers
		$opt['onExpand']	= (array_key_exists('onExpand', $params)) ? '\\'.$params['onExpand'] : null;
		$opt['onSelect']	= (array_key_exists('onSelect', $params)) ? '\\'.$params['onSelect'] : null;
		$opt['onClick']		= (array_key_exists('onClick', $params)) ? '\\'.$params['onClick'] : '\\function(node){  window.open(node.data.url, $chk(node.data.target) ? node.data.target : \'_self\'); }';
		$options = JHtmlBehavior::_getJSObject($opt);

		// Setup root node
		$rt['text']		= (array_key_exists('text', $root)) ? $root['text'] : 'Root';
		$rt['id']		= (array_key_exists('id', $root)) ? $root['id'] : null;
		$rt['color']	= (array_key_exists('color', $root)) ? $root['color'] : null;
		$rt['open']		= (array_key_exists('open', $root)) ? '\\'.$root['open'] : '\\true';
		$rt['icon']		= (array_key_exists('icon', $root)) ? $root['icon'] : null;
		$rt['openicon']	= (array_key_exists('openicon', $root)) ? $root['openicon'] : null;
		$rt['data']		= (array_key_exists('data', $root)) ? $root['data'] : null;
		$rootNode = JHtmlBehavior::_getJSObject($rt);

		$treeName		= (array_key_exists('treeName', $params)) ? $params['treeName'] : '';

		$js = '		window.addEvent(\'domready\', function(){
			tree'.$treeName.' = new MooTreeControl('.$options.','.$rootNode.');
			tree'.$treeName.'.adopt(\''.$id.'\');})';

		// Attach tooltips to document
		$document = &JFactory::getDocument();
		$document->addScriptDeclaration($js);

		// Set static array
		$trees[$id] = true;
		return;
	}

	public static function calendar()
	{
		$document = JFactory::getDocument();
		$tag = JFactory::getLanguage()->getTag();
		JHtml::_('stylesheet','system/calendar-jos.css', array(' title' => JText::_('JLIB_HTML_BEHAVIOR_GREEN') ,' media' => 'all'), true);
		JHtml::_('script',$tag.'/calendar.js', false, true);
		JHtml::_('script',$tag.'/calendar-setup.js', false, true);

		$translation = JHtmlBehavior::_calendartranslation();
		if ($translation) {
			$document->addScriptDeclaration($translation);
		}
	}

	/**
	 * Keep session alive, for example, while editing or creating an article.
	 */
	public static function keepalive()
	{
		// Include mootools framework
		JHtmlBehavior::mootools();

		$config	= &JFactory::getConfig();
		$lifetime	= ($config->get('lifetime') * 60000);
		$refreshTime =  ($lifetime <= 60000) ? 30000 : $lifetime - 60000;
		//refresh time is 1 minute less than the liftime assined in the configuration.php file

		$document = &JFactory::getDocument();
		$script  = '';
		$script .= 'function keepAlive() {';
		$script .=  '	var myAjax = new Ajax("index.php", { method: "get" }).request();';
		$script .=  '}';
		$script .=	' window.addEvent("domready", function()';
		$script .=	'{ keepAlive.periodical('.$refreshTime.'); }';
		$script .=  ');';

		$document->addScriptDeclaration($script);

		return;
	}

	/**
	 * Internal method to get a JavaScript object notation string from an array
	 *
	 * @param	array	$array	The array to convert to JavaScript object notation
	 * @return	string	JavaScript object notation representation of the array
	 * @since	1.5
	 */
	protected static function _getJSObject($array=array())
	{
		// Initialise variables.
		$object = '{';

		// Iterate over array to build objects
		foreach ((array)$array as $k => $v)
		{
			if (is_null($v)) {
				continue;
			}
			if (!is_array($v) && !is_object($v))
			{
				$object .= ' '.$k.': ';
				$object .= (is_numeric($v) || strpos($v, '\\') === 0) ? (is_numeric($v)) ? $v : substr($v, 1) : "'".$v."'";
				$object .= ',';
			}
			else {
				$object .= ' '.$k.': '.JHtmlBehavior::_getJSObject($v).',';
			}
		}
		if (substr($object, -1) == ',') {
			$object = substr($object, 0, -1);
		}
		$object .= '}';

		return $object;
	}

	/**
	 * Internal method to translate the JavaScript Calendar
	 *
	 * @return	string	JavaScript that translates the object
	 * @since	1.5
	 */
	protected static function _calendartranslation()
	{
		static $jsscript = 0;

		if ($jsscript == 0)
		{
			$return = 'Calendar._DN = new Array ("'.JText::_('SUNDAY',true).'", "'.JText::_('MONDAY',true).'", "'.JText::_('TUESDAY',true).'", "'.JText::_('WEDNESDAY',true).'", "'.JText::_('THURSDAY',true).'", "'.JText::_('FRIDAY',true).'", "'.JText::_('SATURDAY',true).'", "'.JText::_('SUNDAY',true).'");Calendar._SDN = new Array ("'.JText::_('SUN',true).'", "'.JText::_('MON',true).'", "'.JText::_('TUE',true).'", "'.JText::_('WED',true).'", "'.JText::_('THU',true).'", "'.JText::_('FRI',true).'", "'.JText::_('SAT',true).'", "'.JText::_('SUN',true).'"); Calendar._FD = 0;	Calendar._MN = new Array ("'.JText::_('JANUARY',true).'", "'.JText::_('FEBRUARY',true).'", "'.JText::_('MARCH',true).'", "'.JText::_('APRIL',true).'", "'.JText::_('MAY',true).'", "'.JText::_('JUNE',true).'", "'.JText::_('JULY',true).'", "'.JText::_('AUGUST',true).'", "'.JText::_('SEPTEMBER',true).'", "'.JText::_('OCTOBER',true).'", "'.JText::_('NOVEMBER',true).'", "'.JText::_('DECEMBER',true).'");	Calendar._SMN = new Array ("'.JText::_('JANUARY_SHORT',true).'", "'.JText::_('FEBRUARY_SHORT',true).'", "'.JText::_('MARCH_SHORT',true).'", "'.JText::_('APRIL_SHORT',true).'", "'.JText::_('MAY_SHORT',true).'", "'.JText::_('JUNE_SHORT',true).'", "'.JText::_('JULY_SHORT',true).'", "'.JText::_('AUGUST_SHORT',true).'", "'.JText::_('SEPTEMBER_SHORT',true).'", "'.JText::_('OCTOBER_SHORT',true).'", "'.JText::_('NOVEMBER_SHORT',true).'", "'.JText::_('DECEMBER_SHORT',true).'");Calendar._TT = {};Calendar._TT["INFO"] = "'.JText::_('JLIB_HTML_BEHAVIOR_ABOUT_THE_CALENDAR',true).'";
		Calendar._TT["ABOUT"] =
 "DHTML Date/Time Selector\n" +
 "(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" +
"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"'.JText::_('JLIB_HTML_BEHAVIOR_DATE_SELECTION',true).'" +
"'.JText::_('JLIB_HTML_BEHAVIOR_YEAR_SELECT',true).'" +
"'.JText::_('JLIB_HTML_BEHAVIOR_MONTH_SELECT',true).'" +
"'.JText::_('JLIB_HTML_BEHAVIOR_HOLD_MOUSE',true).'";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Time selection:\n" +
"- Click on any of the time parts to increase it\n" +
"- or Shift-click to decrease it\n" +
"- or click and drag for faster selection.";

		Calendar._TT["PREV_YEAR"] = "'.JText::_('JLIB_HTML_BEHAVIOR_NEXT_YEAR_HOLD_FOR_MENU',true).'";Calendar._TT["PREV_MONTH"] = "'.JText::_('JLIB_HTML_BEHAVIOR_PREV_MONTH_HOLD_FOR_MENU',true).'";	Calendar._TT["GO_TODAY"] = "'.JText::_('JLIB_HTML_BEHAVIOR_GO_TODAY',true).'";Calendar._TT["NEXT_MONTH"] = "'.JText::_('JLIB_HTML_BEHAVIOR_NEXT_MONTH_HOLD_FOR_MENU',true).'";Calendar._TT["NEXT_YEAR"] = "'.JText::_('JLIB_HTML_BEHAVIOR_NEXT_YEAR_HOLD_FOR_MENU',true).'";Calendar._TT["SEL_DATE"] = "'.JText::_('JLIB_HTML_BEHAVIOR_SELECT_DATE',true).'";Calendar._TT["DRAG_TO_MOVE"] = "'.JText::_('JLIB_HTML_BEHAVIOR_DRAG_TO_MOVE',true).'";Calendar._TT["PART_TODAY"] = "'.JText::_('JLIB_HTML_BEHAVIOR_TODAY',true).'";Calendar._TT["DAY_FIRST"] = "'.JText::_('JLIB_HTML_BEHAVIOR_DISPLAY_S_FIRST',true).'";Calendar._TT["WEEKEND"] = "0,6";Calendar._TT["CLOSE"] = "'.JText::_('JLIB_HTML_BEHAVIOR_CLOSE',true).'";Calendar._TT["TODAY"] = "'.JText::_('JLIB_HTML_BEHAVIOR_TODAY',true).'";Calendar._TT["TIME_PART"] = "'.JText::_('JLIB_HTML_BEHAVIOR_SHIFT_CLICK_OR_DRAG_TO_CHANGE_VALUE',true).'";Calendar._TT["DEF_DATE_FORMAT"] = "'.JText::_('%Y-%m-%d',true).'"; Calendar._TT["TT_DATE_FORMAT"] = "'.JText::_('%a, %b %e',true).'";Calendar._TT["WK"] = "'.JText::_('JLIB_HTML_BEHAVIOR_WK',true).'";Calendar._TT["TIME"] = "'.JText::_('JLIB_HTML_BEHAVIOR_TIME',true).'";';
			$jsscript = 1;
			return $return;
		}
		else {
			return false;
		}
	}
}
