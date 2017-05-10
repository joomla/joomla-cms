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

		// Include core
		static::core();

		JHtml::_('script', 'system/legacy/caption.min.js', array('version' => 'auto', 'relative' => true));

		// Pass the required options to the javascript
		JFactory::getDocument()->addScriptOptions('js-image-caption', ['selector' => $selector]);

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

		// Pass the required options to the javascript
		JFactory::getDocument()->addScriptOptions('js-multiselect', ['formName' => $id]);

		// Set static array
		static::$loaded[__METHOD__][$id] = true;
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

		$app            = JFactory::getApplication();
		$sessionHandler = $app->get('session_handler', 'database');

		// If the handler is not 'Database', we set a fixed, small refresh value (here: 5 min)
		$refreshTime = 300;

		if ($sessionHandler === 'database')
		{
			$lifeTime    = $app->getSession()->getExpire();
			$refreshTime = $lifeTime <= 60 ? 45 : $lifeTime - 60;

			// The longest refresh period is one hour to prevent integer overflow.
			if ($refreshTime > 3600 || $refreshTime <= 0)
			{
				$refreshTime = 3600;
			}
		}

		// If we are in the frontend or logged in as a user, we can use the ajax component to reduce the load
		$uri = 'index.php' . ($app->isClient('site') || !JFactory::getUser()->guest ? '?option=com_ajax&format=json' : '');

		// Include core
		static::core();

		// Add keepalive script options.
		$options = array(
			'interval' => $refreshTime * 1000,
			'uri'      => JRoute::_($uri),
		);
		JFactory::getDocument()->addScriptOptions('system.keepalive', $options);

		// Add script.
		JHtml::_('script', 'system/keepalive.js', array('version' => 'auto', 'relative' => true));

		static::$loaded[__METHOD__] = true;
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

		JHtml::_('script', 'system/highlighter.min.js', array('version' => 'auto', 'relative' => true));

		foreach ($terms as $i => $term)
		{
			$terms[$i] = JFilterOutput::stringJSSafe($term);
		}

		$document = JFactory::getDocument()->addScriptOptions(
			'system.keepalive',
			[
				'start' => $start,
				'end'   => $end,
				'class' => $className,
				'tag'   => $tag,
				'terms' => implode('","', $terms)
			]
		);

		static::$loaded[__METHOD__][$sig] = true;
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
