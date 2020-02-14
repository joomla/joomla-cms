<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('JPATH_BASE') or die;

/**
 * Utility class for Bootstrap elements.
 *
 * @since  3.0
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
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function alert($selector = 'alert')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		JHtml::_('bootstrap.framework');

		JFactory::getDocument()->addScriptOptions('bootstrap.alert', array($selector => ''));

		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add javascript support for Bootstrap buttons
	 *
	 * @param   string  $selector  Common class for the buttons
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function button($selector = 'button')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		JHtml::_('bootstrap.framework');

		JFactory::getDocument()->addScriptOptions('bootstrap.button', array($selector));

		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add javascript support for Bootstrap carousels
	 *
	 * @param   string  $selector  Common class for the carousels.
	 * @param   array   $params    An array of options for the carousel.
	 *                             Options for the carousel can be:
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
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		JHtml::_('bootstrap.framework');

		// Setup options object
		$opt['interval'] = isset($params['interval']) ? (int) $params['interval'] : 5000;
		$opt['pause']    = isset($params['pause']) ? $params['pause'] : 'hover';

		JFactory::getDocument()->addScriptOptions('bootstrap.carousel', array($selector => $opt));

		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add javascript support for Bootstrap dropdowns
	 *
	 * @param   string  $selector  Common class for the dropdowns
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function dropdown($selector = 'dropdown-toggle')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		JHtml::_('bootstrap.framework');

		JFactory::getDocument()->addScriptOptions('bootstrap.dropdown', array($selector));

		static::$loaded[__METHOD__][$selector] = true;
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
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		$debug = (isset($debug) && $debug != JDEBUG) ? $debug : JDEBUG;

		// Load the needed scripts
		JHtml::_('behavior.core');
		JHtml::_('jquery.framework');
		JHtml::_('script', 'vendor/tether/tether.min.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));
		JHtml::_('script', 'vendor/bootstrap/bootstrap.min.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));
		JHtml::_('script', 'system/bootstrap-init.min.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));

		static::$loaded[__METHOD__] = true;
	}

	/**
	 * Method to render a Bootstrap modal
	 *
	 * @param   string  $selector  The ID selector for the modal.
	 * @param   array   $params    An array of options for the modal.
	 *                             Options for the modal can be:
	 *                             - title        string   The modal title
	 *                             - backdrop     mixed    A boolean select if a modal-backdrop element should be included (default = true)
	 *                                                     The string 'static' includes a backdrop which doesn't close the modal on click.
	 *                             - keyboard     boolean  Closes the modal when escape key is pressed (default = true)
	 *                             - closeButton  boolean  Display modal close button (default = true)
	 *                             - animation    boolean  Fade in from the top of the page (default = true)
	 *                             - footer       string   Optional markup for the modal footer
	 *                             - url          string   URL of a resource to be inserted as an `<iframe>` inside the modal body
	 *                             - height       string   height of the `<iframe>` containing the remote resource
	 *                             - width        string   width of the `<iframe>` containing the remote resource
	 * @param   string  $body      Markup for the modal body. Appended after the `<iframe>` if the URL option is set
	 *
	 * @return  string  HTML markup for a modal
	 *
	 * @since   3.0
	 */
	public static function renderModal($selector = 'modal', $params = array(), $body = '')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		JHtml::_('bootstrap.framework');

		$layoutData = array(
			'selector' => $selector,
			'params'   => $params,
			'body'     => $body,
		);

		static::$loaded[__METHOD__][$selector] = true;

		return JLayoutHelper::render('joomla.modal.main', $layoutData);
	}

	/**
	 * Add javascript support for Bootstrap popovers
	 *
	 * Use element's Title as popover content
	 *
	 * @param   string  $selector  Selector for the popover
	 * @param   array   $params    An array of options for the popover.
	 *                  Options for the popover can be:
	 *                      animation    boolean          apply a css fade transition to the popover
	 *                      container    string|boolean   Appends the popover to a specific element: { container: 'body' }
	 *                      content      string|function  default content value if `data-content` attribute isn't present
	 *                      delay        number|object    delay showing and hiding the popover (ms) - does not apply to manual trigger type
	 *                                                    If a number is supplied, delay is applied to both hide/show
	 *                                                    Object structure is: delay: { show: 500, hide: 100 }
	 *                      html         boolean          Insert HTML into the popover. If false, jQuery's text method will be used to insert
	 *                                                    content into the dom.
	 *                      placement    string|function  how to position the popover - top | bottom | left | right
	 *                      selector     string           If a selector is provided, popover objects will be delegated to the specified targets.
	 *                      template     string           Base HTML to use when creating the popover.
	 *                      title        string|function  default title value if `title` tag isn't present
	 *                      trigger      string           how popover is triggered - hover | focus | manual
	 *                      constraints  array            An array of constraints - passed through to Tether.
	 *                      offset       string           Offset of the popover relative to its target.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function popover($selector = '.hasPopover', $params = array())
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		JHtml::_('bootstrap.framework');

		$opt['animation']   = isset($params['animation']) ? $params['animation'] : null;
		$opt['container']   = isset($params['container']) ? $params['container'] : 'body';
		$opt['content']     = isset($params['content']) ? $params['content'] : null;
		$opt['delay']       = isset($params['delay']) ? $params['delay'] : null;
		$opt['html']        = isset($params['html']) ? $params['html'] : true;
		$opt['placement']   = isset($params['placement']) ? $params['placement'] : null;
		$opt['selector']    = isset($params['selector']) ? $params['selector'] : null;
		$opt['template']    = isset($params['template']) ? $params['template'] : null;
		$opt['title']       = isset($params['title']) ? $params['title'] : null;
		$opt['trigger']     = isset($params['trigger']) ? $params['trigger'] : 'hover focus';
		$opt['constraints'] = isset($params['constraints']) ? $params['constraints'] : ['to' => 'scrollParent', 'attachment' => 'together', 'pin' => true];
		$opt['offset']      = isset($params['offset']) ? $params['offset'] : '0 0';


		$opt     = (object) array_filter((array) $opt);

		JFactory::getDocument()->addScriptOptions('bootstrap.popover', array($selector => $opt));

		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add javascript support for Bootstrap ScrollSpy
	 *
	 * @param   string  $selector  The ID selector for the ScrollSpy element.
	 * @param   array   $params    An array of options for the ScrollSpy.
	 *                             Options for the ScrollSpy can be:
	 *                             - offset  number  Pixels to offset from top when calculating position of scroll.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function scrollspy($selector = 'navbar', $params = array())
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		JHtml::_('bootstrap.framework');

		JFactory::getDocument()->addScriptOptions('bootstrap.scrollspy', array($selector => $params));

		static::$loaded[__METHOD__][$selector] = true;
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
	 *                                animation    boolean          apply a css fade transition to the popover
	 *                                container    string|boolean   Appends the popover to a specific element: { container: 'body' }
	 *                                delay        number|object    delay showing and hiding the popover (ms) - does not apply to manual trigger type
	 *                                                              If a number is supplied, delay is applied to both hide/show
	 *                                                              Object structure is: delay: { show: 500, hide: 100 }
	 *                                html         boolean          Insert HTML into the popover. If false, jQuery's text method will be used to insert
	 *                                                              content into the dom.
	 *                                placement    string|function  how to position the popover - top | bottom | left | right
	 *                                selector     string           If a selector is provided, popover objects will be
	 *                                                              delegated to the specified targets.
	 *                                template     string           Base HTML to use when creating the popover.
	 *                                title        string|function  default title value if `title` tag isn't present
	 *                                trigger      string           how popover is triggered - hover | focus | manual
	 *                                constraints  array            An array of constraints - passed through to Tether.
	 *                                offset       string           Offset of the popover relative to its target.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function tooltip($selector = '.hasTooltip', $params = array())
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		JHtml::_('bootstrap.framework');

		// Setup options object
		$opt['animation']   = isset($params['animation']) ? $params['animation'] : null;
		$opt['container']   = isset($params['container']) ? $params['container'] : 'body';
		$opt['delay']       = isset($params['delay']) ? $params['delay'] : null;
		$opt['html']        = isset($params['html']) ? $params['html'] : true;
		$opt['placement']   = isset($params['placement']) ? $params['placement'] : null;
		$opt['selector']    = isset($params['selector']) ? $params['selector'] : null;
		$opt['template']    = isset($params['template']) ? $params['template'] : null;
		$opt['title']       = isset($params['title']) ? $params['title'] : null;
		$opt['trigger']     = isset($params['trigger']) ? $params['trigger'] : 'hover focus';
		$opt['constraints'] = isset($params['constraints']) ? $params['constraints'] : ['to' => 'scrollParent', 'attachment' => 'together', 'pin' => true];
		$opt['offset']      = isset($params['offset']) ? $params['offset'] : '0 0';
		$onShow             = isset($params['onShow']) ? (string) $params['onShow'] : null;
		$onShown            = isset($params['onShown']) ? (string) $params['onShown'] : null;
		$onHide             = isset($params['onHide']) ? (string) $params['onHide'] : null;
		$onHidden           = isset($params['onHidden']) ? (string) $params['onHidden'] : null;


		$opt     = (object) array_filter((array) $opt);

		JFactory::getDocument()->addScriptOptions('bootstrap.tooltip', array($selector => $opt));

		// Set static array
		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Loads js and css files needed by Bootstrap Tooltip Extended plugin
	 *
	 * @param   boolean  $extended  If true, bootstrap-tooltip-extended.js and .css files are loaded
	 *
	 * @return  void
	 *
	 * @since   3.6
	 *
	 * @deprecated  4.0 No replacement, use Bootstrap tooltips.
	 */
	public static function tooltipExtended($extended = true)
	{
		if ($extended)
		{
			JHtml::_('script', 'jui/bootstrap-tooltip-extended.min.js', array('version' => 'auto', 'relative' => true));
			JHtml::_('stylesheet', 'jui/bootstrap-tooltip-extended.css', array('version' => 'auto', 'relative' => true));
		}
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
	 *                             - onShow    function  This event fires immediately when the show instance method is called.
	 *                             - onShown   function  This event is fired when a collapse element has been made visible to the user
	 *                                                   (will wait for css transitions to complete).
	 *                             - onHide    function  This event is fired immediately when the hide method has been called.
	 *                             - onHidden  function  This event is fired when a collapse element has been hidden from the user
	 *                                                   (will wait for css transitions to complete).
	 *
	 * @return  string  HTML for the accordian
	 *
	 * @since   3.0
	 */
	public static function startAccordion($selector = 'myAccordian', $params = array())
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		JHtml::_('bootstrap.framework');

		// Setup options object
		$opt['parent'] = isset($params['parent']) ? ($params['parent'] == true ? '#' . $selector : $params['parent']) : '';
		$opt['toggle'] = isset($params['toggle']) ? (boolean) $params['toggle'] : !($opt['parent'] === false || isset($params['active']));
		$opt['onShow'] = isset($params['onShow']) ? (string) $params['onShow'] : null;
		$opt['onShown'] = isset($params['onShown']) ? (string) $params['onShown'] : null;
		$opt['onHide'] = isset($params['onHide']) ? (string) $params['onHide'] : null;
		$opt['onHidden'] = isset($params['onHidden']) ? (string) $params['onHidden'] : null;

		JFactory::getDocument()->addScriptOptions('bootstrap.accordion', array($selector => $opt));

		static::$loaded[__METHOD__][$selector] = true;

		return '<div id="' . $selector . '" class="accordion" role="tablist">';
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
		return '</div>';
	}

	/**
	 * Begins the display of a new accordion slide.
	 *
	 * @param   string  $selector  Identifier of the accordion group.
	 * @param   string  $text      Text to display.
	 * @param   string  $id        Identifier of the slide.
	 * @param   string  $class     Class of the accordion group.
	 *
	 * @return  string  HTML to add the slide
	 *
	 * @since   3.0
	 */
	public static function addSlide($selector, $text, $id, $class = '')
	{
		$in        = (static::$loaded[__CLASS__ . '::startAccordion'][$selector]['active'] == $id) ? ' in' : '';
		$collapsed = (static::$loaded[__CLASS__ . '::startAccordion'][$selector]['active'] == $id) ? '' : ' collapsed';
		$parent    = static::$loaded[__CLASS__ . '::startAccordion'][$selector]['parent'] ?
			' data-parent="' . static::$loaded[__CLASS__ . '::startAccordion'][$selector]['parent'] . '"' : '';
		$class     = (!empty($class)) ? ' ' . $class : '';

		$html = '<div class="card mb-2' . $class . '">'
			. '<a href="#' . $id . '" data-toggle="collapse"' . $parent . ' class="card-header' . $collapsed . '" role="tab">'
			. $text
			. '</a>'
			. '<div class="collapse' . $in . '" id="' . $id . '" role="tabpanel">'
			. '<div class="card-block">';

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
	 * @since   3.1
	 */
	public static function startTabSet($selector = 'myTab', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (!isset(static::$loaded[__METHOD__][$sig]))
		{
			// Include Bootstrap framework
			JHtml::_('bootstrap.framework');

			// Setup options object
			$opt['active'] = (isset($params['active']) && ($params['active'])) ? (string) $params['active'] : '';

			JFactory::getDocument()->addScriptOptions('bootstrap.tabs', array($selector => $opt));

			// Set static array
			static::$loaded[__METHOD__][$sig] = true;
			static::$loaded[__METHOD__][$selector]['active'] = $opt['active'];
		}

		return JLayoutHelper::render('libraries.cms.html.bootstrap.starttabset', array('selector' => $selector));
	}

	/**
	 * Close the current tab pane
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   3.1
	 */
	public static function endTabSet()
	{
		return JLayoutHelper::render('libraries.cms.html.bootstrap.endtabset');
	}

	/**
	 * Begins the display of a new tab content panel.
	 *
	 * @param   string  $selector  Identifier of the panel.
	 * @param   string  $id        The ID of the div element
	 * @param   string  $title     The title text for the new UL tab
	 *
	 * @return  string  HTML to start a new panel
	 *
	 * @since   3.1
	 */
	public static function addTab($selector, $id, $title)
	{
		static $tabScriptLayout = null;
		static $tabLayout = null;

		$tabScriptLayout = $tabScriptLayout === null ? new JLayoutFile('libraries.cms.html.bootstrap.addtabscript') : $tabScriptLayout;
		$tabLayout = $tabLayout === null ? new JLayoutFile('libraries.cms.html.bootstrap.addtab') : $tabLayout;

		$active = (static::$loaded['JHtmlBootstrap::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

		// Inject tab into UL
		JFactory::getDocument()
			->addScriptDeclaration($tabScriptLayout->render(array('selector' => $selector, 'id' => $id, 'active' => $active, 'title' => $title)));

		return $tabLayout->render(array('id' => $id, 'active' => $active, 'title' => $title));
	}

	/**
	 * Close the current tab content panel
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   3.1
	 */
	public static function endTab()
	{
		return JLayoutHelper::render('libraries.cms.html.bootstrap.endtab');
	}

	/**
	 * Loads CSS files needed by Bootstrap
	 *
	 * @param   boolean  $includeMainCss  If true, main bootstrap.css files are loaded
	 * @param   string   $direction       rtl or ltr direction. If empty, ltr is assumed
	 * @param   array    $attribs         Optional array of attributes to be passed to JHtml::_('stylesheet')
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadCss($includeMainCss = true, $direction = 'ltr', $attribs = array())
	{
		// Load Bootstrap main CSS
		if ($includeMainCss)
		{
			JHtml::_('stylesheet', 'vendor/bootstrap/bootstrap.min.css', array('version' => 'auto', 'relative' => true), $attribs);
		}

		/**
		 * BOOTSTRAP RTL - WILL SORT OUT LATER DOWN THE LINE
		 * Load Bootstrap RTL CSS
		 * if ($direction === 'rtl')
		 * {
		 *  JHtml::_('stylesheet', 'jui/bootstrap-rtl.css', array('version' => 'auto', 'relative' => true), $attribs);
		 * }
		 */

	}
}
