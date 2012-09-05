<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for Bootstrap elements.
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.0
 */
abstract class JHtmlBootstrap
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.0
	 */
	protected static $loaded = array();

	/**
	 * Add javascript support for Bootstrap alerts
	 *
	 * @param   string  $selector  Common class for the alerts

	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function alert($selector = 'alert')
	{
		// Only load once
		if (isset(self::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		self::framework();

		// Attach the alerts to the document
		JFactory::getDocument()->addScriptDeclaration(
			"(function($){
				$('.$selector').alert();
				})(jQuery);"
		);

		self::$loaded[__METHOD__][$selector] = true;

		return;
	}

	/**
	 * Add javascript support for Bootstrap carousels
	 *
	 * @param   string  $selector  Common class for the carousels.
	 * @param   array   $params    An array of options for the modal.
	 *                             Options for the modal can be:
	 *                             - interval  number  The amount of time to delay between automatically cycling an item.
	 *                                                 If false, carousel will not automatically cycle.
	 *                             - pause     string  Pauses the cycling of the carousel on mouseenter and resumes the cycling
	 *                                                 of the carousel on mouseleave.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function carousel($selector = 'carousel', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));
		if (!isset(self::$loaded[__METHOD__][$sig]))
		{
			// Include Bootstrap framework
			self::framework();

			// Setup options object
			$opt['interval'] = (isset($params['interval']) && ($params['interval'])) ? (int) $params['interval'] : 5000;
			$opt['pause'] = (isset($params['pause']) && ($params['pause'])) ? $params['pause'] : 'hover';

			$options = self::_getJSObject($opt);

			// Attach the carousel to document
			JFactory::getDocument()->addScriptDeclaration(
				"(function($){
					$('.$selector').carousel($options);
					})(jQuery);"
			);

			// Set static array
			self::$loaded[__METHOD__][$sig] = true;
		}

		return;
	}

	/**
	 * Add javascript support for Bootstrap dropdowns
	 *
	 * @param   string  $selector  Common class for the dropdowns

	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function dropdown($selector = 'dropdown-toggle')
	{
		// Only load once
		if (isset(self::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		self::framework();

		// Attach the dropdown to the document
		JFactory::getDocument()->addScriptDeclaration(
			"(function($){
				$('.$selector').dropdown();
				})(jQuery);"
		);

		self::$loaded[__METHOD__][$selector] = true;

		return;
	}

	/**
	 * Method to load the Bootstrap JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of Bootstrap is included for easier debugging.
	 *
	 * @param   mixed  $debug  Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function framework($debug = null)
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		// Load jQuery
		JHtml::_('jquery.framework');

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		JHtml::_('script', 'jui/bootstrap.min.js', false, true, false, false, $debug);
		self::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Add javascript support for Bootstrap modals
	 *
	 * @param   string  $selector  The ID selector for the modal.
	 * @param   array   $params    An array of options for the modal.
	 *                             Options for the modal can be:
	 *                             - backdrop  boolean  Includes a modal-backdrop element.
	 *                             - keyboard  boolean  Closes the modal when escape key is pressed.
	 *                             - show      boolean  Shows the modal when initialized.
	 *                             - remote    string   An optional remote URL to load
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function modal($selector = 'modal', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));
		if (!isset(self::$loaded[__METHOD__][$sig]))
		{
			// Include Bootstrap framework
			self::framework();

			// Setup options object
			$opt['backdrop'] = (isset($params['backdrop']) && ($params['backdrop'])) ? (boolean) $params['backdrop'] : true;
			$opt['keyboard'] = (isset($params['keyboard']) && ($params['keyboard'])) ? (boolean) $params['keyboard'] : true;
			$opt['show']     = (isset($params['show']) && ($params['show'])) ? (boolean) $params['show'] : true;
			$opt['remote']   = (isset($params['remote']) && ($params['remote'])) ? (boolean) $params['remote'] : '';

			$options = JHtml::getJSObject($opt);

			// Attach the modal to document
			JFactory::getDocument()->addScriptDeclaration(
				"(function($){
					$('#$selector').modal($options);
					})(jQuery);"
			);

			// Set static array
			self::$loaded[__METHOD__][$sig] = true;
		}

		return;
	}

	/**
	 * Add javascript support for Bootstrap popovers
	 *
	 * Use element's Title as popover content
	 *
	 * @param   string  $selector  Selector for the tooltip
	 * @param   array   $params    An array of options for the tooltip.
	 *                  Options for the tooltip can be:
	 *                      animation  boolean          apply a css fade transition to the tooltip
	 *                      html       boolean          Insert HTML into the tooltip. If false, jQuery's text method will be used to insert
	 *                                                  content into the dom.
	 *                      placement  string|function  how to position the tooltip - top | bottom | left | right
	 *                      selector   string           If a selector is provided, tooltip objects will be delegated to the specified targets.
	 *                      title      string|function  default title value if `title` tag isn't present
	 *                      trigger    string           how tooltip is triggered - hover | focus | manual
	 *                      content    string|function  default content value if `data-content` attribute isn't present
	 *                      delay      number|object    delay showing and hiding the tooltip (ms) - does not apply to manual trigger type
	 *                                                  If a number is supplied, delay is applied to both hide/show
	 *                                                  Object structure is: delay: { show: 500, hide: 100 }
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function popover($selector = '[rel=popover]', $params = array())
	{
		// Only load once
		$sig = md5(serialize(array($selector, $params)));
		if (isset(self::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		// Include Bootstrap framework
		self::framework();

		$opt['animation'] = isset($params['animation']) ? $params['animation'] : true;
		$opt['html']      = isset($params['html']) ? $params['html'] : true;
		$opt['placement'] = isset($params['placement']) ? $params['placement'] : 'top';
		$opt['selector']  = isset($params['selector']) ? $params['selector'] : false;
		$opt['title']     = isset($params['title']) ? $params['title'] : '';
		$opt['trigger']   = isset($params['trigger']) ? $params['trigger'] : 'hover';
		$opt['content']   = isset($params['content']) ? $params['content'] : '';
		$opt['delay']     = isset($params['delay']) ? $params['delay'] : 0;

		$options = JHtml::getJSObject($opt);

		// Attach the popover to the document
		JFactory::getDocument()->addScriptDeclaration(
			"(function($){
				$('#$selector').popover($options);
				})(jQuery);"
		);

		self::$loaded[__METHOD__][$sig] = true;

		return;
	}

	/**
	 * Add javascript support for Bootstrap ScrollSpy
	 *
	 * @param   string  $selector  The ID selector for the ScrollSpy element.
	 * @param   array   $params    An array of options for the ScrollSpy.
	 *                             Options for the modal can be:
	 *                             - offset  number  Pixels to offset from top when calculating position of scroll.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function scrollspy($selector = 'navbar', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));
		if (!isset(self::$loaded[__METHOD__][$sig]))
		{
			// Include Bootstrap framework
			self::framework();

			// Setup options object
			$opt['offset'] = (isset($params['offset']) && ($params['offset'])) ? (int) $params['offset'] : 10;

			$options = JHtml::getJSObject($opt);

			// Attach ScrollSpy to document
			JFactory::getDocument()->addScriptDeclaration(
				"(function($){
					$('#$selector').scrollspy($options);
					})(jQuery);"
			);

			// Set static array
			self::$loaded[__METHOD__][$sig] = true;
		}

		return;
	}

	/**
	 * Add javascript support for Bootstrap tooltips
	 *
	 * Add a title attribute to any element in the form
	 * title="title::text"
	 *
	 * @param   string  $selector  The ID selector for the tooltip.
	 * @param   array   $params    An array of options for the tooltip.
	 *                             Options for the tooltip can be:
	 *                             - animation  boolean          Apply a CSS fade transition to the tooltip
	 *                             - html       boolean          Insert HTML into the tooltip. If false, jQuery's text method will be used to insert
	 *                                                           content into the dom.
	 *                             - placement  string|function  How to position the tooltip - top | bottom | left | right
	 *                             - selector   string           If a selector is provided, tooltip objects will be delegated to the specified targets.
	 *                             - title      string|function  Default title value if `title` tag isn't present
	 *                             - trigger    string           How tooltip is triggered - hover | focus | manual
	 *                             - delay      number           Delay showing and hiding the tooltip (ms) - does not apply to manual trigger type
	 *                                                           If a number is supplied, delay is applied to both hide/show
	 *                                                           Object structure is: delay: { show: 500, hide: 100 }
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function tooltip($selector = 'content', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));
		if (!isset(self::$loaded[__METHOD__][$sig]))
		{
			// Include Bootstrap framework
			self::framework();

			// Setup options object
			$opt['animation'] = (isset($params['animation']) && ($params['animation'])) ? (boolean) $params['animation'] : true;
			$opt['html']      = (isset($params['html']) && ($params['html'])) ? (boolean) $params['html'] : true;
			$opt['placement'] = (isset($params['placement']) && ($params['placement'])) ? (string) $params['placement'] : 'top';
			$opt['selector']  = (isset($params['selector']) && ($params['selector'])) ? (string) $params['selector'] : false;
			$opt['title']     = (isset($params['title']) && ($params['title'])) ? (string) $params['title'] : '';
			$opt['trigger']   = (isset($params['trigger']) && ($params['trigger'])) ? (string) $params['trigger'] : 'hover';
			$opt['delay']     = (isset($params['delay']) && ($params['delay'])) ? (int) $params['delay'] : 0;

			$options = JHtml::getJSObject($opt);

			// Attach tooltips to document
			JFactory::getDocument()->addScriptDeclaration(
				"(function($){
					$('#$selector').tooltip($options);
					})(jQuery);"
			);

			// Set static array
			self::$loaded[__METHOD__][$sig] = true;
		}

		return;
	}

	/**
	 * Add javascript support for Bootstrap accordians and insert the accordian
	 *
	 * @param   string  $selector  The ID selector for the tooltip.
	 * @param   array   $params    An array of options for the tooltip.
	 *                             Options for the tooltip can be:
	 *                             - parent  selector  If selector then all collapsible elements under the specified parent will be closed when this
	 *                                                 collapsible item is shown. (similar to traditional accordion behavior)
	 *                             - toggle  boolean   Toggles the collapsible element on invocation
	 *                             - active  string    Sets the active slide during load
	 *
	 * @return  string  HTML for the accordian
	 *
	 * @since   3.0
	 */
	public static function startAccordion($selector = 'myAccordian', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));
		if (!isset(self::$loaded[__METHOD__][$sig]))
		{
			// Include Bootstrap framework
			self::framework();

			// Setup options object
			$opt['parent'] = (isset($params['parent']) && ($params['parent'])) ? (boolean) $params['parent'] : false;
			$opt['toggle'] = (isset($params['toggle']) && ($params['toggle'])) ? (boolean) $params['toggle'] : true;
			$opt['active'] = (isset($params['active']) && ($params['active'])) ? (string) $params['active'] : '';

			$options = JHtml::getJSObject($opt);

			// Attach accordion to document
			JFactory::getDocument()->addScriptDeclaration(
				"(function($){
					$('#$selector').collapse($options);
				})(jQuery);"
			);

			// Set static array
			self::$loaded[__METHOD__][$sig] = true;
			self::$loaded[__METHOD__]['active'] = $opt['active'];
		}

		return '<div id="' . $selector . '" class="accordion">';
	}

	/**
	 * Close the current accordion
	 *
	 * @return  string  HTML to close the accordian
	 *
	 * @since   3.0
	 */
	public static function endAccordion()
	{
		return '</div></div>';
	}

	/**
	 * Begins the display of a new accordion slide.
	 *
	 * @param   string  $selector  Identifier of the accordion group.
	 * @param   string  $text      Text to display.
	 * @param   string  $id        Identifier of the slide.
	 *
	 * @return  string  HTML to add the slide
	 *
	 * @since   3.0
	 */
	public static function addSlide($selector, $text, $id)
	{
		$in = (self::$loaded['JHtmlBootstrap::startAccordion']['active'] == $id) ? ' in' : '';

		$html = '<div class="accordion-group">'
				. '<div class="accordion-heading">'
				. '<strong><a href="#' . $id . '" data-parent="#' . $selector . '" data-toggle="collapse" class="accordion-toggle">'
				. $text
				. '</a></strong>'
				. '</div>'
				. '<div class="accordion-body collapse' . $in . '" id="' . $id . '">'
				. '<div class="accordion-inner">';

		return $html;
	}

	/**
	 * Close the current slide
	 *
	 * @return  string  HTML to close the slide
	 *
	 * @since   3.0
	 */
	public static function endSlide()
	{
		return '</div></div></div>';
	}

	/**
	 * Creates a tab pane
	 *
	 * @param   string  $selector  The pane identifier.
	 * @param   array   $params    The parameters for the pane
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public static function startPane($selector = 'myTab', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));
		if (!isset(self::$loaded[__METHOD__][$sig]))
		{
			// Include Bootstrap framework
			self::framework();

			// Setup options object
			$opt['active'] = (isset($params['active']) && ($params['active'])) ? (string) $params['active'] : '';

			$options = JHtml::getJSObject($opt);

			// Attach tooltips to document
			JFactory::getDocument()->addScriptDeclaration(
				"(function($){
					$('#$selector a').click(function (e) {
						e.preventDefault();
						$(this).tab('show');
					});
				})(jQuery);"
			);

			// Set static array
			self::$loaded[__METHOD__][$sig] = true;
			self::$loaded[__METHOD__][$selector]['active'] = $opt['active'];
		}

		return '<div class="tab-content" id="' . $selector . 'Content">';
	}

	/**
	 * Close the current tab pane
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   3.0
	 */
	public static function endPane()
	{
		return '</div>';
	}

	/**
	 * Begins the display of a new tab content panel.
	 *
	 * @param   string  $selector  Identifier of the panel.
	 * @param   string  $id        The ID of the div element
	 *
	 * @return  string  HTML to start a new panel
	 *
	 * @since   3.0
	 */
	public static function addPanel($selector, $id)
	{
		$active = (self::$loaded['JHtmlBootstrap::startPane'][$selector]['active'] == $id) ? ' active' : '';

		return '<div id="' . $id . '" class="tab-pane' . $active . '">';
	}

	/**
	 * Close the current tab content panel
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   3.0
	 */
	public static function endPanel()
	{
		return '</div>';
	}
}
