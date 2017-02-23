<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
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
		// Files removed!!
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

		JHtml::_('script', 'system/core.min.js', array('version' => 'auto', 'relative' => true));
		static::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Add unobtrusive JavaScript support for image captions.
	 *
	 * @param   string  $selector  The selector for which a caption behaviour is to be applied.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function caption($selector = 'img.caption')
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		JHtml::_('script', 'system/legacy/caption.min.js', array('version' => 'auto', 'relative' => true));

		// Attach caption to document
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
document.addEventListener('DOMContentLoaded',  function() {
	new JCaption('$selector');
});
JS
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

		// Add validate.js language strings
		JText::script('JLIB_FORM_CONTAINS_INVALID_FIELDS');
		JText::script('JLIB_FORM_FIELD_REQUIRED_VALUE');
		JText::script('JLIB_FORM_FIELD_REQUIRED_CHECK');
		JText::script('JLIB_FORM_FIELD_INVALID_VALUE');

		JHtml::_('script', 'vendor/punycode/punycode.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('script', 'system/fields/validate.min.js', array('version' => 'auto', 'relative' => true));

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
		// Files removed!
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

		JHtml::_('stylesheet', 'vendor/awesomplete/awesomplete.css', array('version' => 'auto', 'relative' => true));
		JHtml::_('script', 'vendor/awesomplete/awesomplete.js', array('version' => 'auto', 'relative' => true));

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
		// Files removed!!
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
		// Files removed!!
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

		JHtml::_('script', 'system/multiselect.min.js', array('version' => 'auto', 'relative' => true));

		// Attach multiselect to document
		JFactory::getDocument()->addScriptDeclaration(
			"document.addEventListener('DOMContentLoaded', function() {
				Joomla.JMultiSelect('" . $id . "');
			});"
		);

		// Set static array
		static::$loaded[__METHOD__][$id] = true;
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
		// Files removed!!
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
		JLog::add('JHtmlBehavior::calendar is deprecated as the static assets are being loaded in the relative layout.', JLog::WARNING, 'deprecated');
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

		JHtml::_('script', 'vendor/minicolors/jquery.minicolors.min.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('stylesheet', 'vendor/minicolors/jquery.minicolors.css', array('version' => 'auto', 'relative' => true));
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

		JHtml::_('script', 'system/js/fields/jquery.simplecolors.min.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('stylesheet', 'system/js/fields/jquery.simplecolors.css', array('version' => 'auto', 'relative' => true));
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

		$app = JFactory::getApplication();

		// If the handler is not 'Database', we set a fixed, small refresh value (here: 5 min)
		if ($app->get('session_handler', 'filesystem') != 'database')
		{
			$refreshTime = 300000;
		}
		else
		{
			$life_time   = $app->getSession()->getExpire() * 1000;
			$refreshTime = ($life_time <= 60000) ? 45000 : $life_time - 60000;

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

		JHtml::_('script', 'system/highlighter.min.js', array('version' => 'auto', 'relative' => true));

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
	 * @deprecated  13.3 (Platform) & 4.0 (CMS) - Use JHtml::getJSObject() instead.
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

		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/tabs-state.min.js', array('version' => 'auto', 'relative' => true));
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
		if (is_null($polyfillTypes))
		{
			return false;
		}

		if (!is_array($polyfillTypes))
		{
			$polyfillTypes = array($polyfillTypes);
		}

		foreach ($polyfillTypes as $polyfillType)
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

			JHtml::_('script', 'vendor/polyfills/polyfill.' . $polyfillType . '.js', $scriptOptions);

			// Set static array
			static::$loaded[__METHOD__][$sig] = true;
		}
	}
}
