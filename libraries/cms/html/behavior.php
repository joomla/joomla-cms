<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for JavaScript behaviors
 *
 * @since  1.5
 */
abstract class JHtmlBehavior
{
	/**
	 * Array containing information for loaded files
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected static $loaded = array();

	/**
	 * Method to load the MooTools framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of MooTools is included for easier debugging.
	 *
	 * @param   boolean  $extras  Flag to determine whether to load MooTools More in addition to Core
	 * @param   mixed    $debug   Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @deprecated 4.0 Update scripts to jquery
	 */
	public static function framework($extras = false, $debug = null)
	{
		$type = $extras ? 'more' : 'core';

		// Only load once
		if (!empty(static::$loaded[__METHOD__][$type]))
		{
			return;
		}

		JLog::add('JHtmlBehavior::framework is deprecated. Update to jquery scripts.', JLog::WARNING, 'deprecated');

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$debug = JDEBUG;
		}

		if ($type !== 'core' && empty(static::$loaded[__METHOD__]['core']))
		{
			static::framework(false, $debug);
		}

		JHtml::_('script', 'system/mootools-' . $type . '.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));

		// Keep loading core.js for BC reasons
		static::core();

		static::$loaded[__METHOD__][$type] = true;

		return;
	}

	/**
	 * Method to load core.js into the document head.
	 *
	 * Core.js defines the 'Joomla' namespace and contains functions which are used across extensions
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public static function core()
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		JHtml::_('form.csrf');
		JHtml::_('script', 'system/core.js', array('version' => 'auto', 'relative' => true));

		// Add core and base uri paths so javascript scripts can use them.
		JFactory::getDocument()->addScriptOptions('system.paths', array('root' => JUri::root(true), 'base' => JUri::base(true)));

		static::$loaded[__METHOD__] = true;
	}

	/**
	 * Add unobtrusive JavaScript support for image captions.
	 *
	 * @param   string  $selector  The selector for which a caption behaviour is to be applied.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @Deprecated 4.0 Use native HTML figure tags.
	 */
	public static function caption($selector = 'img.caption')
	{
		JLog::add('JHtmlBehavior::caption is deprecated. Use native HTML figure tags.', JLog::WARNING, 'deprecated');

		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include jQuery
		JHtml::_('jquery.framework');

		JHtml::_('script', 'system/caption.js', array('version' => 'auto', 'relative' => true));

		// Attach caption to document
		JFactory::getDocument()->addScriptDeclaration(
			"jQuery(window).on('load',  function() {
				new JCaption('" . $selector . "');
			});"
		);

		// Set static array
		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add unobtrusive JavaScript support for form validation.
	 *
	 * To enable form validation the form tag must have class="form-validate".
	 * Each field that needs to be validated needs to have class="validate".
	 * Additional handlers can be added to the handler for username, password,
	 * numeric and email. To use these add class="validate-email" and so on.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @Deprecated 3.4 Use formvalidator instead
	 */
	public static function formvalidation()
	{
		JLog::add('The use of formvalidation is deprecated use formvalidator instead.', JLog::WARNING, 'deprecated');

		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Include MooTools framework
		static::framework();

		// Load the new jQuery code
		static::formvalidator();
	}

	/**
	 * Add unobtrusive JavaScript support for form validation.
	 *
	 * To enable form validation the form tag must have class="form-validate".
	 * Each field that needs to be validated needs to have class="validate".
	 * Additional handlers can be added to the handler for username, password,
	 * numeric and email. To use these add class="validate-email" and so on.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public static function formvalidator()
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Include core
		static::core();

		// Include jQuery
		JHtml::_('jquery.framework');

		// Add validate.js language strings
		JText::script('JLIB_FORM_FIELD_INVALID');

		JHtml::_('script', 'system/punycode.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('script', 'system/validate.js', array('version' => 'auto', 'relative' => true));
		static::$loaded[__METHOD__] = true;
	}

	/**
	 * Add unobtrusive JavaScript support for submenu switcher support
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function switcher()
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Include jQuery
		JHtml::_('jquery.framework');

		JHtml::_('script', 'system/switcher.js', array('framework' => true, 'version' => 'auto', 'relative' => true));

		$script = "
			document.switcher = null;
			jQuery(function($){
				var toggler = document.getElementById('submenu');
				var element = document.getElementById('config-document');
				if (element) {
					document.switcher = new JSwitcher(toggler, element);
				}
			});";

		JFactory::getDocument()->addScriptDeclaration($script);
		static::$loaded[__METHOD__] = true;
	}

	/**
	 * Add unobtrusive JavaScript support for a combobox effect.
	 *
	 * Note that this control is only reliable in absolutely positioned elements.
	 * Avoid using a combobox in a slider or dynamic pane.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function combobox()
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}
		// Include core
		static::core();

		JHtml::_('script', 'system/combobox.js', array('version' => 'auto', 'relative' => true));
		static::$loaded[__METHOD__] = true;
	}

	/**
	 * Add unobtrusive JavaScript support for a hover tooltips.
	 *
	 * Add a title attribute to any element in the form
	 * title="title::text"
	 *
	 * Uses the core Tips class in MooTools.
	 *
	 * @param   string  $selector  The class selector for the tooltip.
	 * @param   array   $params    An array of options for the tooltip.
	 *                             Options for the tooltip can be:
	 *                             - maxTitleChars  integer   The maximum number of characters in the tooltip title (defaults to 50).
	 *                             - offsets        object    The distance of your tooltip from the mouse (defaults to {'x': 16, 'y': 16}).
	 *                             - showDelay      integer   The millisecond delay the show event is fired (defaults to 100).
	 *                             - hideDelay      integer   The millisecond delay the hide hide is fired (defaults to 100).
	 *                             - className      string    The className your tooltip container will get.
	 *                             - fixed          boolean   If set to true, the toolTip will not follow the mouse.
	 *                             - onShow         function  The default function for the show event, passes the tip element
	 *                               and the currently hovered element.
	 *                             - onHide         function  The default function for the hide event, passes the currently
	 *                               hovered element.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function tooltip($selector = '.hasTip', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		// Include MooTools framework
		static::framework(true);

		// Setup options object
		$opt['maxTitleChars'] = isset($params['maxTitleChars']) && $params['maxTitleChars'] ? (int) $params['maxTitleChars'] : 50;

		// Offsets needs an array in the format: array('x'=>20, 'y'=>30)
		$opt['offset']    = isset($params['offset']) && is_array($params['offset']) ? $params['offset'] : null;
		$opt['showDelay'] = isset($params['showDelay']) ? (int) $params['showDelay'] : null;
		$opt['hideDelay'] = isset($params['hideDelay']) ? (int) $params['hideDelay'] : null;
		$opt['className'] = isset($params['className']) ? $params['className'] : null;
		$opt['fixed']     = isset($params['fixed']) && $params['fixed'];
		$opt['onShow']    = isset($params['onShow']) ? '\\' . $params['onShow'] : null;
		$opt['onHide']    = isset($params['onHide']) ? '\\' . $params['onHide'] : null;

		$options = JHtml::getJSObject($opt);

		// Include jQuery
		JHtml::_('jquery.framework');

		// Attach tooltips to document
		JFactory::getDocument()->addScriptDeclaration(
			"jQuery(function($) {
			 $('$selector').each(function() {
				var title = $(this).attr('title');
				if (title) {
					var parts = title.split('::', 2);
					var mtelement = document.id(this);
					mtelement.store('tip:title', parts[0]);
					mtelement.store('tip:text', parts[1]);
				}
			});
			var JTooltips = new Tips($('$selector').get(), $options);
		});"
		);

		// Set static array
		static::$loaded[__METHOD__][$sig] = true;

		return;
	}

	/**
	 * Add unobtrusive JavaScript support for modal links.
	 *
	 * @param   string  $selector  The selector for which a modal behaviour is to be applied.
	 * @param   array   $params    An array of parameters for the modal behaviour.
	 *                             Options for the modal behaviour can be:
	 *                            - ajaxOptions
	 *                            - size
	 *                            - shadow
	 *                            - overlay
	 *                            - onOpen
	 *                            - onClose
	 *                            - onUpdate
	 *                            - onResize
	 *                            - onShow
	 *                            - onHide
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated 4.0  Use the modal equivalent from bootstrap
	 */
	public static function modal($selector = 'a.modal', $params = array())
	{
		$document = JFactory::getDocument();

		// Load the necessary files if they haven't yet been loaded
		if (!isset(static::$loaded[__METHOD__]))
		{
			// Include MooTools framework
			static::framework(true);

			// Load the JavaScript and css
			JHtml::_('script', 'system/modal.js', array('framework' => true, 'version' => 'auto', 'relative' => true));
			JHtml::_('stylesheet', 'system/modal.css', array('version' => 'auto', 'relative' => true));
		}

		$sig = md5(serialize(array($selector, $params)));

		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		JLog::add('JHtmlBehavior::modal is deprecated. Use the modal equivalent from bootstrap.', JLog::WARNING, 'deprecated');

		// Setup options object
		$opt['ajaxOptions']   = isset($params['ajaxOptions']) && is_array($params['ajaxOptions']) ? $params['ajaxOptions'] : null;
		$opt['handler']       = isset($params['handler']) ? $params['handler'] : null;
		$opt['parseSecure']   = isset($params['parseSecure']) ? (bool) $params['parseSecure'] : null;
		$opt['closable']      = isset($params['closable']) ? (bool) $params['closable'] : null;
		$opt['closeBtn']      = isset($params['closeBtn']) ? (bool) $params['closeBtn'] : null;
		$opt['iframePreload'] = isset($params['iframePreload']) ? (bool) $params['iframePreload'] : null;
		$opt['iframeOptions'] = isset($params['iframeOptions']) && is_array($params['iframeOptions']) ? $params['iframeOptions'] : null;
		$opt['size']          = isset($params['size']) && is_array($params['size']) ? $params['size'] : null;
		$opt['shadow']        = isset($params['shadow']) ? $params['shadow'] : null;
		$opt['overlay']       = isset($params['overlay']) ? $params['overlay'] : null;
		$opt['onOpen']        = isset($params['onOpen']) ? $params['onOpen'] : null;
		$opt['onClose']       = isset($params['onClose']) ? $params['onClose'] : null;
		$opt['onUpdate']      = isset($params['onUpdate']) ? $params['onUpdate'] : null;
		$opt['onResize']      = isset($params['onResize']) ? $params['onResize'] : null;
		$opt['onMove']        = isset($params['onMove']) ? $params['onMove'] : null;
		$opt['onShow']        = isset($params['onShow']) ? $params['onShow'] : null;
		$opt['onHide']        = isset($params['onHide']) ? $params['onHide'] : null;

		// Include jQuery
		JHtml::_('jquery.framework');

		if (isset($params['fullScreen']) && (bool) $params['fullScreen'])
		{
			$opt['size']      = array('x' => '\\jQuery(window).width() - 80', 'y' => '\\jQuery(window).height() - 80');
		}

		$options = JHtml::getJSObject($opt);

		// Attach modal behavior to document
		$document
			->addScriptDeclaration(
			"
		jQuery(function($) {
			SqueezeBox.initialize(" . $options . ");
			SqueezeBox.assign($('" . $selector . "').get(), {
				parse: 'rel'
			});
		});

		window.jModalClose = function () {
			SqueezeBox.close();
		};
		
		// Add extra modal close functionality for tinyMCE-based editors
		document.onreadystatechange = function () {
			if (document.readyState == 'interactive' && typeof tinyMCE != 'undefined' && tinyMCE)
			{
				if (typeof window.jModalClose_no_tinyMCE === 'undefined')
				{	
					window.jModalClose_no_tinyMCE = typeof(jModalClose) == 'function'  ?  jModalClose  :  false;
					
					jModalClose = function () {
						if (window.jModalClose_no_tinyMCE) window.jModalClose_no_tinyMCE.apply(this, arguments);
						tinyMCE.activeEditor.windowManager.close();
					};
				}
		
				if (typeof window.SqueezeBoxClose_no_tinyMCE === 'undefined')
				{
					if (typeof(SqueezeBox) == 'undefined')  SqueezeBox = {};
					window.SqueezeBoxClose_no_tinyMCE = typeof(SqueezeBox.close) == 'function'  ?  SqueezeBox.close  :  false;
		
					SqueezeBox.close = function () {
						if (window.SqueezeBoxClose_no_tinyMCE)  window.SqueezeBoxClose_no_tinyMCE.apply(this, arguments);
						tinyMCE.activeEditor.windowManager.close();
					};
				}
			}
		};
		"
		);

		// Set static array
		static::$loaded[__METHOD__][$sig] = true;

		return;
	}

	/**
	 * JavaScript behavior to allow shift select in grids
	 *
	 * @param   string  $id  The id of the form for which a multiselect behaviour is to be applied.
	 *
	 * @return  void
	 *
	 * @since   1.7
	 */
	public static function multiselect($id = 'adminForm')
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$id]))
		{
			return;
		}

		// Include core
		static::core();

		// Include jQuery
		JHtml::_('jquery.framework');

		JHtml::_('script', 'system/multiselect.js', array('version' => 'auto', 'relative' => true));

		// Attach multiselect to document
		JFactory::getDocument()->addScriptDeclaration(
			"jQuery(document).ready(function() {
				Joomla.JMultiSelect('" . $id . "');
			});"
		);

		// Set static array
		static::$loaded[__METHOD__][$id] = true;

		return;
	}

	/**
	 * Add unobtrusive javascript support for a collapsible tree.
	 *
	 * @param   string  $id      An index
	 * @param   array   $params  An array of options.
	 * @param   array   $root    The root node
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function tree($id, $params = array(), $root = array())
	{
		// Include MooTools framework
		static::framework();

		JHtml::_('script', 'system/mootree.js', array('framework' => true, 'version' => 'auto', 'relative' => true));
		JHtml::_('stylesheet', 'system/mootree.css', array('version' => 'auto', 'relative' => true));

		if (isset(static::$loaded[__METHOD__][$id]))
		{
			return;
		}

		// Include jQuery
		JHtml::_('jquery.framework');

		// Setup options object
		$opt['div']   = array_key_exists('div', $params) ? $params['div'] : $id . '_tree';
		$opt['mode']  = array_key_exists('mode', $params) ? $params['mode'] : 'folders';
		$opt['grid']  = array_key_exists('grid', $params) ? '\\' . $params['grid'] : true;
		$opt['theme'] = array_key_exists('theme', $params) ? $params['theme'] : JHtml::_('image', 'system/mootree.gif', '', array(), true, true);

		// Event handlers
		$opt['onExpand'] = array_key_exists('onExpand', $params) ? '\\' . $params['onExpand'] : null;
		$opt['onSelect'] = array_key_exists('onSelect', $params) ? '\\' . $params['onSelect'] : null;
		$opt['onClick']  = array_key_exists('onClick', $params) ? '\\' . $params['onClick']
		: '\\function(node){  window.open(node.data.url, node.data.target != null ? node.data.target : \'_self\'); }';

		$options = JHtml::getJSObject($opt);

		// Setup root node
		$rt['text']     = array_key_exists('text', $root) ? $root['text'] : 'Root';
		$rt['id']       = array_key_exists('id', $root) ? $root['id'] : null;
		$rt['color']    = array_key_exists('color', $root) ? $root['color'] : null;
		$rt['open']     = array_key_exists('open', $root) ? '\\' . $root['open'] : true;
		$rt['icon']     = array_key_exists('icon', $root) ? $root['icon'] : null;
		$rt['openicon'] = array_key_exists('openicon', $root) ? $root['openicon'] : null;
		$rt['data']     = array_key_exists('data', $root) ? $root['data'] : null;
		$rootNode = JHtml::getJSObject($rt);

		$treeName = array_key_exists('treeName', $params) ? $params['treeName'] : '';

		$js = '		jQuery(function(){
			tree' . $treeName . ' = new MooTreeControl(' . $options . ',' . $rootNode . ');
			tree' . $treeName . '.adopt(\'' . $id . '\');})';

		// Attach tooltips to document
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($js);

		// Set static array
		static::$loaded[__METHOD__][$id] = true;

		return;
	}

	/**
	 * Add unobtrusive JavaScript support for a calendar control.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @deprecated 4.0
	 */
	public static function calendar()
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		JLog::add('JHtmlBehavior::calendar is deprecated as the static assets are being loaded in the relative layout.', JLog::WARNING, 'deprecated');

		$document = JFactory::getDocument();
		$tag      = JFactory::getLanguage()->getTag();
		$attribs  = array('title' => JText::_('JLIB_HTML_BEHAVIOR_GREEN'), 'media' => 'all');

		JHtml::_('stylesheet', 'system/calendar-jos.css', array('version' => 'auto', 'relative' => true), $attribs);
		JHtml::_('script', $tag . '/calendar.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('script', $tag . '/calendar-setup.js', array('version' => 'auto', 'relative' => true));

		$translation = static::calendartranslation();

		if ($translation)
		{
			$document->addScriptDeclaration($translation);
		}

		static::$loaded[__METHOD__] = true;
	}

	/**
	 * Add unobtrusive JavaScript support for a color picker.
	 *
	 * @return  void
	 *
	 * @since   1.7
	 *
	 * @deprecated 4.0 Use directly the field or the layout
	 */
	public static function colorpicker()
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Include jQuery
		JHtml::_('jquery.framework');

		JHtml::_('script', 'jui/jquery.minicolors.min.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('stylesheet', 'jui/jquery.minicolors.css', array('version' => 'auto', 'relative' => true));
		JFactory::getDocument()->addScriptDeclaration("
				jQuery(document).ready(function (){
					jQuery('.minicolors').each(function() {
						jQuery(this).minicolors({
							control: jQuery(this).attr('data-control') || 'hue',
							format: jQuery(this).attr('data-validate') === 'color'
								? 'hex'
								: (jQuery(this).attr('data-format') === 'rgba'
									? 'rgb'
									: jQuery(this).attr('data-format'))
								|| 'hex',
							keywords: jQuery(this).attr('data-keywords') || '',
							opacity: jQuery(this).attr('data-format') === 'rgba' ? true : false || false,
							position: jQuery(this).attr('data-position') || 'default',
							theme: 'bootstrap'
						});
					});
				});
			"
		);

		static::$loaded[__METHOD__] = true;
	}

	/**
	 * Add unobtrusive JavaScript support for a simple color picker.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 *
	 * @deprecated 4.0 Use directly the field or the layout
	 */
	public static function simplecolorpicker()
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Include jQuery
		JHtml::_('jquery.framework');

		JHtml::_('script', 'jui/jquery.simplecolors.min.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('stylesheet', 'jui/jquery.simplecolors.css', array('version' => 'auto', 'relative' => true));
		JFactory::getDocument()->addScriptDeclaration("
				jQuery(document).ready(function (){
					jQuery('select.simplecolors').simplecolors();
				});
			"
		);

		static::$loaded[__METHOD__] = true;
	}

	/**
	 * Keep session alive, for example, while editing or creating an article.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function keepalive()
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		$session = JFactory::getSession();

		// If the handler is not 'Database', we set a fixed, small refresh value (here: 5 min)
		$refreshTime = 300;

		if ($session->storeName === 'database')
		{
			$lifeTime    = $session->getExpire();
			$refreshTime = $lifeTime <= 60 ? 45 : $lifeTime - 60;

			// The longest refresh period is one hour to prevent integer overflow.
			if ($refreshTime > 3600 || $refreshTime <= 0)
			{
				$refreshTime = 3600;
			}
		}

		// If we are in the frontend or logged in as a user, we can use the ajax component to reduce the load
		$uri = 'index.php' . (JFactory::getApplication()->isClient('site') || !JFactory::getUser()->guest ? '?option=com_ajax&format=json' : '');

		// Include core and polyfill for browsers lower than IE 9.
		static::core();
		static::polyfill('event', 'lt IE 9');

		// Add keepalive script options.
		JFactory::getDocument()->addScriptOptions('system.keepalive', array('interval' => $refreshTime * 1000, 'uri' => JRoute::_($uri)));

		// Add script.
		JHtml::_('script', 'system/keepalive.js', array('version' => 'auto', 'relative' => true));

		static::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Highlight some words via Javascript.
	 *
	 * @param   array   $terms      Array of words that should be highlighted.
	 * @param   string  $start      ID of the element that marks the begin of the section in which words
	 *                              should be highlighted. Note this element will be removed from the DOM.
	 * @param   string  $end        ID of the element that end this section.
	 *                              Note this element will be removed from the DOM.
	 * @param   string  $className  Class name of the element highlights are wrapped in.
	 * @param   string  $tag        Tag that will be used to wrap the highlighted words.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function highlighter(array $terms, $start = 'highlighter-start', $end = 'highlighter-end', $className = 'highlight', $tag = 'span')
	{
		$sig = md5(serialize(array($terms, $start, $end)));

		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		$terms = array_filter($terms, 'strlen');

		// Nothing to Highlight
		if (empty($terms))
		{
			static::$loaded[__METHOD__][$sig] = true;

			return;
		}

		// Include core
		static::core();

		// Include jQuery
		JHtml::_('jquery.framework');

		JHtml::_('script', 'system/highlighter.js', array('version' => 'auto', 'relative' => true));

		foreach ($terms as $i => $term)
		{
			$terms[$i] = JFilterOutput::stringJSSafe($term);
		}

		$document = JFactory::getDocument();
		$document->addScriptDeclaration("
			jQuery(function ($) {
				var start = document.getElementById('" . $start . "');
				var end = document.getElementById('" . $end . "');
				if (!start || !end || !Joomla.Highlighter) {
					return true;
				}
				highlighter = new Joomla.Highlighter({
					startElement: start,
					endElement: end,
					className: '" . $className . "',
					onlyWords: false,
					tag: '" . $tag . "'
				}).highlight([\"" . implode('","', $terms) . "\"]);
				$(start).remove();
				$(end).remove();
			});
		");

		static::$loaded[__METHOD__][$sig] = true;

		return;
	}

	/**
	 * Break us out of any containing iframes
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @deprecated  4.0  Add a X-Frame-Options HTTP Header with the SAMEORIGIN value instead.
	 */
	public static function noframes()
	{
		JLog::add(__METHOD__ . ' is deprecated, add a X-Frame-Options HTTP Header with the SAMEORIGIN value instead.', JLog::WARNING, 'deprecated');

		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Include core
		static::core();

		// Include jQuery
		JHtml::_('jquery.framework');

		$js = 'jQuery(function () {
			if (top == self) {
				document.documentElement.style.display = "block";
			}
			else
			{
				top.location = self.location;
			}

			// Firefox fix
			jQuery("input[autofocus]").focus();
		})';
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('html { display:none }');
		$document->addScriptDeclaration($js);

		JFactory::getApplication()->setHeader('X-Frame-Options', 'SAMEORIGIN');

		static::$loaded[__METHOD__] = true;
	}

	/**
	 * Internal method to get a JavaScript object notation string from an array
	 *
	 * @param   array  $array  The array to convert to JavaScript object notation
	 *
	 * @return  string  JavaScript object notation representation of the array
	 *
	 * @since       1.5
	 * @deprecated  4.0 - Use JHtml::getJSObject() instead.
	 */
	protected static function _getJSObject($array = array())
	{
		JLog::add('JHtmlBehavior::_getJSObject() is deprecated. JHtml::getJSObject() instead..', JLog::WARNING, 'deprecated');

		return JHtml::getJSObject($array);
	}

	/**
	 * Add unobtrusive JavaScript support to keep a tab state.
	 *
	 * Note that keeping tab state only works for inner tabs if in accordance with the following example:
	 *
	 * ```
	 * parent tab = permissions
	 * child tab = permission-<identifier>
	 * ```
	 *
	 * Each tab header `<a>` tag also should have a unique href attribute
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function tabstate()
	{
		if (isset(self::$loaded[__METHOD__]))
		{
			return;
		}
		// Include jQuery
		JHtml::_('jquery.framework');
		JHtml::_('behavior.polyfill', array('filter','xpath'));
		JHtml::_('script', 'system/tabs-state.js', array('version' => 'auto', 'relative' => true));
		self::$loaded[__METHOD__] = true;
	}

	/**
	 * Add javascript polyfills.
	 *
	 * @param   string|array  $polyfillTypes       The polyfill type(s). Examples: event, array('event', 'classlist').
	 * @param   string        $conditionalBrowser  An IE conditional expression. Example: lt IE 9 (lower than IE 9).
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public static function polyfill($polyfillTypes = null, $conditionalBrowser = null)
	{
		if ($polyfillTypes === null)
		{
			return;
		}

		foreach ((array) $polyfillTypes as $polyfillType)
		{
			$sig = md5(serialize(array($polyfillType, $conditionalBrowser)));

			// Only load once
			if (isset(static::$loaded[__METHOD__][$sig]))
			{
				continue;
			}

			// If include according to browser.
			$scriptOptions = array('version' => 'auto', 'relative' => true);
			$scriptOptions = $conditionalBrowser !== null ? array_replace($scriptOptions, array('conditional' => $conditionalBrowser)) : $scriptOptions;

			JHtml::_('script', 'system/polyfill.' . $polyfillType . '.js', $scriptOptions);

			// Set static array
			static::$loaded[__METHOD__][$sig] = true;
		}
	}

	/**
	 * Internal method to translate the JavaScript Calendar
	 *
	 * @return  string  JavaScript that translates the object
	 *
	 * @since   1.5
	 */
	protected static function calendartranslation()
	{
		static $jsscript = 0;

		// Guard clause, avoids unnecessary nesting
		if ($jsscript)
		{
			return false;
		}

		$jsscript = 1;

		// To keep the code simple here, run strings through JText::_() using array_map()
		$callback = array('JText', '_');
		$weekdays_full = array_map(
			$callback, array(
				'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY',
			)
		);
		$weekdays_short = array_map(
			$callback,
			array(
				'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN',
			)
		);
		$months_long = array_map(
			$callback, array(
				'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
				'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER',
			)
		);
		$months_short = array_map(
			$callback, array(
				'JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT', 'MAY_SHORT', 'JUNE_SHORT',
				'JULY_SHORT', 'AUGUST_SHORT', 'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT',
			)
		);

		// This will become an object in Javascript but define it first in PHP for readability
		$today = " " . JText::_('JLIB_HTML_BEHAVIOR_TODAY') . " ";
		$text = array(
			'INFO'           => JText::_('JLIB_HTML_BEHAVIOR_ABOUT_THE_CALENDAR'),
			'ABOUT'          => "DHTML Date/Time Selector\n"
				. "(c) dynarch.com 20022005 / Author: Mihai Bazon\n"
				. "For latest version visit: http://www.dynarch.com/projects/calendar/\n"
				. "Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details."
				. "\n\n"
				. JText::_('JLIB_HTML_BEHAVIOR_DATE_SELECTION')
				. JText::_('JLIB_HTML_BEHAVIOR_YEAR_SELECT')
				. JText::_('JLIB_HTML_BEHAVIOR_MONTH_SELECT')
				. JText::_('JLIB_HTML_BEHAVIOR_HOLD_MOUSE'),
			'ABOUT_TIME'      => "\n\n"
				. "Time selection:\n"
				. " Click on any of the time parts to increase it\n"
				. " or Shiftclick to decrease it\n"
				. " or click and drag for faster selection.",
			'PREV_YEAR'       => JText::_('JLIB_HTML_BEHAVIOR_PREV_YEAR_HOLD_FOR_MENU'),
			'PREV_MONTH'      => JText::_('JLIB_HTML_BEHAVIOR_PREV_MONTH_HOLD_FOR_MENU'),
			'GO_TODAY'        => JText::_('JLIB_HTML_BEHAVIOR_GO_TODAY'),
			'NEXT_MONTH'      => JText::_('JLIB_HTML_BEHAVIOR_NEXT_MONTH_HOLD_FOR_MENU'),
			'SEL_DATE'        => JText::_('JLIB_HTML_BEHAVIOR_SELECT_DATE'),
			'DRAG_TO_MOVE'    => JText::_('JLIB_HTML_BEHAVIOR_DRAG_TO_MOVE'),
			'PART_TODAY'      => $today,
			'DAY_FIRST'       => JText::_('JLIB_HTML_BEHAVIOR_DISPLAY_S_FIRST'),
			'WEEKEND'         => JFactory::getLanguage()->getWeekEnd(),
			'CLOSE'           => JText::_('JLIB_HTML_BEHAVIOR_CLOSE'),
			'TODAY'           => JText::_('JLIB_HTML_BEHAVIOR_TODAY'),
			'TIME_PART'       => JText::_('JLIB_HTML_BEHAVIOR_SHIFT_CLICK_OR_DRAG_TO_CHANGE_VALUE'),
			'DEF_DATE_FORMAT' => "%Y%m%d",
			'TT_DATE_FORMAT'  => JText::_('JLIB_HTML_BEHAVIOR_TT_DATE_FORMAT'),
			'WK'              => JText::_('JLIB_HTML_BEHAVIOR_WK'),
			'TIME'            => JText::_('JLIB_HTML_BEHAVIOR_TIME'),
		);

		return 'Calendar._DN = ' . json_encode($weekdays_full) . ';'
			. ' Calendar._SDN = ' . json_encode($weekdays_short) . ';'
			. ' Calendar._FD = 0;'
			. ' Calendar._MN = ' . json_encode($months_long) . ';'
			. ' Calendar._SMN = ' . json_encode($months_short) . ';'
			. ' Calendar._TT = ' . json_encode($text) . ';';
	}
}
