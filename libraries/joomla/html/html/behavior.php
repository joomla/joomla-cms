<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for javascript behaviors
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @version		11.1
 */
abstract class JHtmlBehavior
{
	/**
	 * Method to load the MooTools framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of MooTools is included for easier debugging.
	 *
	 * @param   string   $extras	MooTools file to load
	 * @param   boolean  $debug	Is debugging mode on? [optional]
	 *
	 * @return  void
	 * @since   11.1
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
		if ($debug === null) {
			$config = JFactory::getConfig();
			$debug = $config->get('debug');
		}

		$uncompressed	= $debug ? '-uncompressed' : '';

		if ($type != 'core' && empty($loaded['core'])) {
			self::framework(false, $debug);
		}

		JHtml::_('script', 'system/mootools-'.$type.$uncompressed.'.js', false, true, false, false);
		$loaded[$type] = true;

		return;
	}

	/**
	 * Deprecated. Use JHtmlBehavior::framework() instead.
	 *
	 * @param   boolean  $debug	Is debugging mode on? [optional]
	 * @deprecated    12.1
	 * @return  void
	 * @since   11.1
	 */
	public static function mootools($debug = null)
	{
		self::framework(true, $debug);
	}

	/**
	 * Add unobtrusive javascript support for image captions.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function caption()
	{
		static $loaded = false;

		// Only load once
		if ($loaded) {
			return;
		}

		// Include MooTools framework
		self::framework();

		$uncompressed = JFactory::getConfig()->get('debug') ? '-uncompressed' : '';
		JHtml::_('script', 'system/caption'.$uncompressed.'.js', true, true);
		$loaded = true;
	}

	/**
	 * Add unobtrusive javascript support for form validation.
	 *
	 * To enable form validation the form tag must have class="form-validate".
	 * Each field that needs to be validated need to have class="validate".
	 * Additional handlers can be added to the handler for username, password,
	 * numeric and email. To use these add class="validate-email" and so on.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function formvalidation()
	{
		static $loaded = false;

		// Only load once
		if ($loaded) {
			return;
		}

		// Include MooTools framework
		self::framework();

		$uncompressed = JFactory::getConfig()->get('debug') ? '-uncompressed' : '';
		JHtml::_('script', 'system/validate'.$uncompressed.'.js', true, true);
		$loaded = true;
	}

	/**
	 * Add unobtrusive javascript support for submenu switcher support in
	 * Global Configuration and System Information.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function switcher()
	{
		static $loaded = false;

		// Only load once
		if ($loaded) {
			return;
		}

		// Include MooTools framework
		self::framework();

		$uncompressed = JFactory::getConfig()->get('debug') ? '-uncompressed' : '';
		JHtml::_('script', 'system/switcher'.$uncompressed.'.js', true, true);

		$script = "
			document.switcher = null;
			window.addEvent('domready', function(){
				toggler = document.id('submenu');
				element = document.id('config-document');
				if (element) {
					document.switcher = new JSwitcher(toggler, element, {cookieName: toggler.getProperty('class')});
				}
			});";

		JFactory::getDocument()->addScriptDeclaration($script);
		$loaded = true;
	}

	/**
	 * Add unobtrusive javascript support for a combobox effect.
	 *
	 * Note that this control is only reliable in absolutely positioned elements.
	 * Avoid using a combobox in a slider or dynamic pane.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function combobox()
	{
		static $loaded = false;

		// Only load once
		if ($loaded) {
			return;
		}

		// Include MooTools framework
		self::framework();

		$uncompressed = JFactory::getConfig()->get('debug') ? '-uncompressed' : '';
		JHtml::_('script', 'system/combobox'.$uncompressed.'.js', true, true);
		$loaded = true;
	}

	/**
	 * Add unobtrusive javascript support for a hover tooltips.
	 *
	 * Add a title attribute to any element in the form
	 * title="title::text"
	 *
	 * Options for the tooltip can be:
	 * - maxTitleChars	int		The maximum number of characters in the tooltip title (defaults to 50).
	 * - offsets		object	The distance of your tooltip from the mouse (defaults to {'x': 16, 'y': 16}).
	 * - showDelay		int		The millisecond delay the show event is fired (defaults to 100).
	 * - hideDelay		int		The millisecond delay the hide hide is fired (defaults to 100).
	 * - className		string	The className your tooltip container will get.
	 * - fixed			boolean	If set to true, the toolTip will not follow the mouse.
	 * - onShow			func	The default function for the show event, passes the tip element and the currently hovered element.
	 * - onHide			func	The default function for the hide event, passes the currently hovered element.
	 *
	 * Uses the core Tips class in MooTools.
	 *
	 * @param   string   $selector	The class selector for the tooltip.
	 * @param   array    $params		An array of options for the tooltip.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function tooltip($selector='.hasTip', $params = array())
	{
		static $tips;

		if (!isset($tips)) {
			$tips = array();
		}

		// Include MooTools framework
		self::framework(true);

		$sig = md5(serialize(array($selector,$params)));
		if (isset($tips[$sig]) && ($tips[$sig])) {
			return;
		}

		// Setup options object
		$opt['maxTitleChars']	= (isset($params['maxTitleChars']) && ($params['maxTitleChars'])) ? (int)$params['maxTitleChars'] : 50 ;
		// offsets needs an array in the format: array('x'=>20, 'y'=>30)
		$opt['offset']			= (isset($params['offset']) && (is_array($params['offset']))) ? $params['offset'] : null;
		if (!isset($opt['offset'])) {
			// Suppporting offsets parameter which was working in mootools 1.2 (Joomla!1.5)
			$opt['offset']		= (isset($params['offsets']) && (is_array($params['offsets']))) ? $params['offsets'] : null;
		}
		$opt['showDelay']		= (isset($params['showDelay'])) ? (int)$params['showDelay'] : null;
		$opt['hideDelay']		= (isset($params['hideDelay'])) ? (int)$params['hideDelay'] : null;
		$opt['className']		= (isset($params['className'])) ? $params['className'] : null;
		$opt['fixed']			= (isset($params['fixed']) && ($params['fixed'])) ? '\\true' : '\\false';
		$opt['onShow']			= (isset($params['onShow'])) ? '\\'.$params['onShow'] : null;
		$opt['onHide']			= (isset($params['onHide'])) ? '\\'.$params['onHide'] : null;

		$options = JHtmlBehavior::_getJSObject($opt);

		// Attach tooltips to document
		$document = JFactory::getDocument();
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

	/**
	 * Add unobtrusive javascript support for modal links.
	 *
	 * Options for the modal behaviour can be:
	 * - ajaxOptions
	 * - size
	 * - shadow
	 * - onOpen
	 * - onClose
	 * - onUpdate
	 * - onResize
	 * - onShow
	 * - onHide
	 *
	 * @param   string  $selector	The class selector for which a modal behaviour is to be applied.
	 * @param   array   $params		An array of parameters for the modal behaviour.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function modal($selector = 'a.modal', $params = array())
	{
		static $modals;
		static $included;

		$document = JFactory::getDocument();

		// Load the necessary files if they haven't yet been loaded
		if (!isset($included)) {
			// Include MooTools framework
			self::framework();

			// Load the javascript and css
			$uncompressed	= JFactory::getConfig()->get('debug') ? '-uncompressed' : '';
			JHtml::_('script', 'system/modal'.$uncompressed.'.js', true, true);
			JHtml::_('stylesheet', 'system/modal.css', array(), true);

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
		$opt['ajaxOptions']		= (isset($params['ajaxOptions']) && (is_array($params['ajaxOptions']))) ? $params['ajaxOptions'] : null;
		$opt['handler']			= (isset($params['handler'])) ? $params['handler'] : null;
		$opt['fullScreen']  	= (isset($params['fullScreen'])) ? (bool) $params['fullScreen'] : null;
		$opt['parseSecure']  	= (isset($params['parseSecure'])) ? (bool) $params['parseSecure'] : null;
		$opt['closable']  		= (isset($params['closable'])) ? (bool) $params['closable'] : null;
		$opt['closeBtn']  		= (isset($params['closeBtn'])) ? (bool) $params['closeBtn'] : null;
		$opt['iframePreload']  	= (isset($params['iframePreload'])) ? (bool) $params['iframePreload'] : null;
		$opt['iframeOptions']	= (isset($params['iframeOptions']) && (is_array($params['iframeOptions']))) ? $params['iframeOptions'] : null;
		$opt['size']			= (isset($params['size']) && (is_array($params['size']))) ? $params['size'] : null;
		$opt['shadow']			= (isset($params['shadow'])) ? $params['shadow'] : null;
		$opt['onOpen']			= (isset($params['onOpen'])) ? $params['onOpen'] : null;
		$opt['onClose']			= (isset($params['onClose'])) ? $params['onClose'] : null;
		$opt['onUpdate']		= (isset($params['onUpdate'])) ? $params['onUpdate'] : null;
		$opt['onResize']		= (isset($params['onResize'])) ? $params['onResize'] : null;
		$opt['onMove']			= (isset($params['onMove'])) ? $params['onMove'] : null;
		$opt['onShow']			= (isset($params['onShow'])) ? $params['onShow'] : null;
		$opt['onHide']			= (isset($params['onHide'])) ? $params['onHide'] : null;

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

	/**
	 * JavaScript behavior to allow shift select in grids
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function multiselect()
	{
		// Include MooTools framework
		self::framework();
		JHtml::_('script', 'system/multiselect.js', true, true);

		return;
	}

	/**
	 * Add unobtrusive javascript support for the advanced uploader.
	 *
	 * @param   string  $id
	 * @param   array   $params	An array of options for the uploader.
	 * @param   string  $upload_queue
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function uploader($id='file-upload', $params = array(), $upload_queue='upload-queue')
	{
		// Include MooTools framework
		self::framework();

		$uncompressed	= JFactory::getConfig()->get('debug') ? '-uncompressed' : '';
		JHtml::_('script', 'system/swf'.$uncompressed.'.js', true, true);
		JHtml::_('script', 'system/progressbar'.$uncompressed.'.js', true, true);
		JHtml::_('script', 'system/uploader'.$uncompressed.'.js', true, true);

		$document = JFactory::getDocument();

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
			JText::script('JLIB_HTML_BEHAVIOR_UPLOADER_ALL_FILES');
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
		$opt['target']				= (isset($params['target'])) ? $params['target'] : '\\document.id(\'upload-browse\')';
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
		$opt['typeFilter']			= (isset($params['types'])) ? '\\'.$params['types'] : '\\{Joomla.JText._(\'JLIB_HTML_BEHAVIOR_UPLOADER_ALL_FILES\'): \'*.*\'}';
		$opt['typeFilter']			= (isset($params['typeFilter'])) ? '\\'.$params['typeFilter'] : $opt['typeFilter'];

		// Optional functions
		$opt['createReplacement'] 	= (isset($params['createReplacement'])) ? '\\'.$params['createReplacement'] : null;
		$opt['onFileComplete'] 		= (isset($params['onFileComplete'])) ? '\\'.$params['onFileComplete'] : null;
		$opt['onBeforeStart'] 		= (isset($params['onBeforeStart'])) ? '\\'.$params['onBeforeStart'] : null;
		$opt['onStart'] 			= (isset($params['onStart'])) ? '\\'.$params['onStart'] : null;
		$opt['onComplete'] 			= (isset($params['onComplete'])) ? '\\'.$params['onComplete'] : null;
		$opt['onFileSuccess'] 		= (isset($params['onFileSuccess'])) ? '\\'.$params['onFileSuccess'] : $onFileSuccess;

		if (!isset($params['startButton'])) {
			$params['startButton'] = 'upload-start';
		}

		if (!isset($params['clearButton'])) {
			$params['clearButton'] = 'upload-clear';
		}

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
				var Uploader = new FancyUpload2(document.id(\''.$id.'\'), document.id(\''.$upload_queue.'\'), '.$options.' );
				});';
		$document->addScriptDeclaration($uploaderInit);

		// Set static array
		$uploaders[$id] = true;

		return;
	}

	/**
	 * Add unobtrusive javascript support for a collapsible tree.
	 *
	 * @param   $id		string
	 * @param   $params	array	An array of options.
	 * @param   $root	array
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function tree($id, $params = array(), $root = array())
	{
		static $trees;

		if (!isset($trees)) {
			$trees = array();
		}

		// Include MooTools framework
		self::framework();

		$uncompressed	= JFactory::getConfig()->get('debug') ? '-uncompressed' : '';
		JHtml::_('script', 'system/mootree'.$uncompressed.'.js', true, true, false, false);
		JHtml::_('stylesheet', 'system/mootree.css', array(), true);

		if (isset($trees[$id]) && ($trees[$id])) {
			return;
		}

		// Setup options object
		$opt['div']		= (array_key_exists('div', $params)) ? $params['div'] : $id.'_tree';
		$opt['mode']	= (array_key_exists('mode', $params)) ? $params['mode'] : 'folders';
		$opt['grid']	= (array_key_exists('grid', $params)) ? '\\'.$params['grid'] : '\\true';
		$opt['theme']	= (array_key_exists('theme', $params)) ? $params['theme'] : JHtml::_('image', 'system/mootree.gif', '', array(), true, true);

		// Event handlers
		$opt['onExpand']	= (array_key_exists('onExpand', $params)) ? '\\'.$params['onExpand'] : null;
		$opt['onSelect']	= (array_key_exists('onSelect', $params)) ? '\\'.$params['onSelect'] : null;
		$opt['onClick']		= (array_key_exists('onClick', $params))
						? '\\'.$params['onClick']
						: '\\function(node){  window.open(node.data.url, $chk(node.data.target) ? node.data.target : \'_self\'); }';

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
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($js);

		// Set static array
		$trees[$id] = true;

		return;
	}

	/**
	 * Add unobtrusive javascript support for a calendar control.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function calendar()
	{
		static $loaded = false;

		// Only load once
		if ($loaded) {
			return;
		}

		$document		= JFactory::getDocument();
		$tag			= JFactory::getLanguage()->getTag();

		//Add uncompressed versions when debug is enabled
		$uncompressed	= JFactory::getConfig()->get('debug') ? '-uncompressed' : '';
		JHtml::_('stylesheet', 'system/calendar-jos.css', array(' title' => JText::_('JLIB_HTML_BEHAVIOR_GREEN') ,' media' => 'all'), true);
		JHtml::_('script', $tag.'/calendar'.$uncompressed.'.js', false, true);
		JHtml::_('script', $tag.'/calendar-setup'.$uncompressed.'.js', false, true);

		$translation = JHtmlBehavior::_calendartranslation();
		if ($translation) {
			$document->addScriptDeclaration($translation);
		}
		$loaded = true;
	}

	/**
	 * Keep session alive, for example, while editing or creating an article.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function keepalive()
	{
		static $loaded = false;

		// Only load once
		if ($loaded) {
			return;
		}

		// Include MooTools framework
		self::framework();

		$config		= JFactory::getConfig();
		$lifetime	= ($config->get('lifetime') * 60000);
		$refreshTime =  ($lifetime <= 60000) ? 30000 : $lifetime - 60000;
		// Refresh time is 1 minute less than the liftime assined in the configuration.php file.

		// the longest refresh period is one hour to prevent integer overflow.
		if ($refreshTime > 3600000 || $refreshTime <= 0) {
			$refreshTime = 3600000;
		}

		$document = JFactory::getDocument();
		$script  = '';
		$script .= 'function keepAlive() {';
		$script .=  '	var myAjax = new Request({method: "get", url: "index.php"}).send();';
		$script .=  '}';
		$script .=	' window.addEvent("domready", function()';
		$script .=	'{ keepAlive.periodical('.$refreshTime.'); }';
		$script .=  ');';

		$document->addScriptDeclaration($script);
		$loaded = true;

		return;
	}

	/**
	 * Break us out of any containing iframes
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function noframes($location='top.location.href')
	{
		static $loaded = false;

		// Only load once
		if ($loaded) {
			return;
		}

		// Include MooTools framework
		self::framework();

		$js = "window.addEvent('domready', function () {if (top == self) {document.documentElement.style.display = 'block'; } else {top.location = self.location; }});";
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('html { display:none }');
		$document->addScriptDeclaration($js);

		JResponse::setHeader('X-Frames-Options', 'SAME-ORIGIN');

		$loaded = true;
	}

	/**
	 * Internal method to get a JavaScript object notation string from an array
	 *
	 * @param   array  $array	The array to convert to JavaScript object notation
	 *
	 * @return  string  JavaScript object notation representation of the array
	 * @since   11.1
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

			if (is_bool($v)) {
				if ($k === 'fullScreen') {
					$object .= 'size: { ';
					$object .= 'x: ';
					$object .= 'window.getSize().x-80';
					$object .= ',';
					$object .= 'y: ';
					$object .= 'window.getSize().y-80';
					$object .= ' }';
					$object .= ',';
				}
				else {
					$object .= ' '.$k.': ';
					$object .= ($v) ? 'true' : 'false';
					$object .= ',';
				}
			}
			else if (!is_array($v) && !is_object($v)) {
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
	 * @return  string  JavaScript that translates the object
	 * @since   11.1
	 */
	protected static function _calendartranslation()
	{
		static $jsscript = 0;

		if ($jsscript == 0) {
			$return =
			'Calendar._DN = new Array ("'
				.JText::_('SUNDAY', true).'", "'
				.JText::_('MONDAY', true).'", "'
				.JText::_('TUESDAY', true).'", "'
				.JText::_('WEDNESDAY', true).'", "'
				.JText::_('THURSDAY', true).'", "'
				.JText::_('FRIDAY', true).'", "'
				.JText::_('SATURDAY', true).'", "'
				.JText::_('SUNDAY', true).'");'
			.' Calendar._SDN = new Array ("'
				.JText::_('SUN', true).'", "'
				.JText::_('MON', true).'", "'
				.JText::_('TUE', true).'", "'
				.JText::_('WED', true).'", "'
				.JText::_('THU', true).'", "'
				.JText::_('FRI', true).'", "'
				.JText::_('SAT', true).'", "'
				.JText::_('SUN', true).'");'
			.' Calendar._FD = 0;'
			.' Calendar._MN = new Array ("'
				.JText::_('JANUARY', true).'", "'
				.JText::_('FEBRUARY', true).'", "'
				.JText::_('MARCH', true).'", "'
				.JText::_('APRIL', true).'", "'
				.JText::_('MAY', true).'", "'
				.JText::_('JUNE', true).'", "'
				.JText::_('JULY', true).'", "'
				.JText::_('AUGUST', true).'", "'
				.JText::_('SEPTEMBER', true).'", "'
				.JText::_('OCTOBER', true).'", "'
				.JText::_('NOVEMBER', true).'", "'
				.JText::_('DECEMBER', true).'");'
			.' Calendar._SMN = new Array ("'
				.JText::_('JANUARY_SHORT', true).'", "'
				.JText::_('FEBRUARY_SHORT', true).'", "'
				.JText::_('MARCH_SHORT', true).'", "'
				.JText::_('APRIL_SHORT', true).'", "'
				.JText::_('MAY_SHORT', true).'", "'
				.JText::_('JUNE_SHORT', true).'", "'
				.JText::_('JULY_SHORT', true).'", "'
				.JText::_('AUGUST_SHORT', true).'", "'
				.JText::_('SEPTEMBER_SHORT', true).'", "'
				.JText::_('OCTOBER_SHORT', true).'", "'
				.JText::_('NOVEMBER_SHORT', true).'", "'
				.JText::_('DECEMBER_SHORT', true).'");'
			.' Calendar._TT = {};Calendar._TT["INFO"] = "'.JText::_('JLIB_HTML_BEHAVIOR_ABOUT_THE_CALENDAR', true).'";'
			.' Calendar._TT["ABOUT"] =
 "DHTML Date/Time Selector\n" +
 "(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" +
"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"'.JText::_('JLIB_HTML_BEHAVIOR_DATE_SELECTION', true).'" +
"'.JText::_('JLIB_HTML_BEHAVIOR_YEAR_SELECT', true).'" +
"'.JText::_('JLIB_HTML_BEHAVIOR_MONTH_SELECT', true).'" +
"'.JText::_('JLIB_HTML_BEHAVIOR_HOLD_MOUSE', true).'";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Time selection:\n" +
"- Click on any of the time parts to increase it\n" +
"- or Shift-click to decrease it\n" +
"- or click and drag for faster selection.";

		Calendar._TT["PREV_YEAR"] = "'.JText::_('JLIB_HTML_BEHAVIOR_PREV_YEAR_HOLD_FOR_MENU', true).'";'
		.' Calendar._TT["PREV_MONTH"] = "'.JText::_('JLIB_HTML_BEHAVIOR_PREV_MONTH_HOLD_FOR_MENU', true).'";'
		.' Calendar._TT["GO_TODAY"] = "'.JText::_('JLIB_HTML_BEHAVIOR_GO_TODAY', true).'";'
		.' Calendar._TT["NEXT_MONTH"] = "'.JText::_('JLIB_HTML_BEHAVIOR_NEXT_MONTH_HOLD_FOR_MENU', true).'";'
		.' Calendar._TT["NEXT_YEAR"] = "'.JText::_('JLIB_HTML_BEHAVIOR_NEXT_YEAR_HOLD_FOR_MENU', true).'";'
		.' Calendar._TT["SEL_DATE"] = "'.JText::_('JLIB_HTML_BEHAVIOR_SELECT_DATE', true).'";'
		.' Calendar._TT["DRAG_TO_MOVE"] = "'.JText::_('JLIB_HTML_BEHAVIOR_DRAG_TO_MOVE', true).'";'
		.' Calendar._TT["PART_TODAY"] = "'.JText::_('JLIB_HTML_BEHAVIOR_TODAY', true).'";'
		.' Calendar._TT["DAY_FIRST"] = "'.JText::_('JLIB_HTML_BEHAVIOR_DISPLAY_S_FIRST', true).'";'
		.' Calendar._TT["WEEKEND"] = "0,6";'
		.' Calendar._TT["CLOSE"] = "'.JText::_('JLIB_HTML_BEHAVIOR_CLOSE', true).'";'
		.' Calendar._TT["TODAY"] = "'.JText::_('JLIB_HTML_BEHAVIOR_TODAY', true).'";'
		.' Calendar._TT["TIME_PART"] = "'.JText::_('JLIB_HTML_BEHAVIOR_SHIFT_CLICK_OR_DRAG_TO_CHANGE_VALUE', true).'";'
		.' Calendar._TT["DEF_DATE_FORMAT"] = "'.JText::_('%Y-%m-%d', true).'";'
		.' Calendar._TT["TT_DATE_FORMAT"] = "'.JText::_('%a, %b %e', true).'";'
		.' Calendar._TT["WK"] = "'.JText::_('JLIB_HTML_BEHAVIOR_WK', true).'";'
		.' Calendar._TT["TIME"] = "'.JText::_('JLIB_HTML_BEHAVIOR_TIME', true).'";';
			$jsscript = 1;
			return $return;
		}
		else {
			return false;
		}
	}
}
