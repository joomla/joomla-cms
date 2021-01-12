<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;

/**
 * Utility class for Bootstrap elements.
 *
 * @since  3.0
 */
abstract class Bootstrap
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.0
	 */
	protected static $loaded = [];

	/**
	 * @var    array  Array containing the available components
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $scripts = [
		'alert',
		'button',
		'carousel',
		'collapse',
		'dropdown',
		'modal',
		'popover',
		'scrollspy',
		'tab',
		'toast',
		'tooltip',
	];

	/**
	 * @var    array  Array containing the components loaded
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $loadedScripts = [];

	/**
	 * Add javascript support for Bootstrap alerts
	 *
	 * @param   string  $selector  Common class for the alerts
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 *
	 * @since   3.0
	 */
	public static function alert($selector = '.alert')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'alert');

		Factory::getDocument()->addScriptOptions('bootstrap.alert', [$selector]);

		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add javascript support for Bootstrap buttons
	 *
	 * @param   string  $selector  Common class for the buttons
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 *
	 * @since   3.1
	 */
	public static function button($selector = '.button')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'button');

		Factory::getDocument()->addScriptOptions('bootstrap.button', [$selector]);

		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add javascript support for Bootstrap carousels
	 *
	 * @param   string  $selector  Common class for the carousels.
	 * @param   array   $params    An array of options for the carousel.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 *
	 * @since   3.0
	 *
	 * Options for the carousel can be:
	 * - interval  number  The amount of time to delay between automatically cycling an item.
	 *                     If false, carousel will not automatically cycle.
	 * - pause     string  Pauses the cycling of the carousel on mouseenter and resumes the cycling
	 *                     of the carousel on mouseleave.
	 */
	public static function carousel($selector = '.carousel', $params = [])
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		HTMLHelper::_('bootstrap.loadScript', 'carousel');

		// Setup options object
		$opt['interval'] = isset($params['interval']) ? (int) $params['interval'] : 5000;
		$opt['pause']    = isset($params['pause']) ? $params['pause'] : 'hover';

		Factory::getDocument()->addScriptOptions('bootstrap.carousel', [$selector => $opt]);

		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add javascript support for Bootstrap collapse
	 *
	 * @param   string  $selector  Common class for the collapse
	 * @param   array   $params    Parameters for the collapsable element
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function collapse($selector = '.collapse', $params = [])
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'collapse');

		// Setup options object
		$opt['interval'] = isset($params['interval']) ? (int) $params['interval'] : 5000;
		$opt['pause']    = isset($params['pause']) ? $params['pause'] : 'hover';

		Factory::getDocument()->addScriptOptions('bootstrap.carousel', [$selector => $opt]);

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
	public static function dropdown($selector = '.dropdown-toggle')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'dropdown');

		Factory::getDocument()->addScriptOptions('bootstrap.dropdown', [$selector]);

		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Method to enqueue a javascript file
	 *
	 * @param   string $script The component name
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	public static function loadScript(string $script)
	{
		if (!in_array($script, static::$loadedScripts)
			&& in_array($script, static::$scripts))
		{
			// Tooltip+popover are combined
			$script = $script === 'tooltip' ? 'popover' : $script;

			// @todo use a json file
			Factory::getApplication()
				->getDocument()
				->getWebAssetManager()
				->registerScript(
					'bootstrap.' . $script . '.ES6',
					'vendor/bs5/' . $script . '.es6.min.js',
					[
						'dependencies' => [],
						'attributes' => [
							'type' => 'module'
						]
					]
				)
				->useScript('bootstrap.' . $script . '.ES6');

			// @todo ES5 as nomodule/defer
			array_push(static::$loadedScripts, $script);
		}
	}

	/**
	 * Method is EMPTY!!!
	 *
	 * @param   mixed $debug Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function framework($debug = null)
	{
		Log::add(
			'Bootstrap is using modular scripts in Joomla 4. Nothing loaded!',
			Log::WARNING,
			'deprecated'
		);
	}

	/**
	 * Method to render a Bootstrap modal
	 *
	 * @param   string  $selector  The ID selector for the modal.
	 * @param   array   $params    An array of options for the modal.
	 * @param   string  $body      Markup for the modal body. Appended after the `<iframe>` if the URL option is set
	 *
	 * @return  string  HTML markup for a modal
	 *
	 * @since   3.0
	 *
	 * Options ($param) for the modal can be:
	 * - title        string   The modal title
	 * - backdrop     mixed    A boolean select if a modal-backdrop element should be included (default = true)
	 *                         The string 'static' includes a backdrop which doesn't close the modal on click.
	 * - keyboard     boolean  Closes the modal when escape key is pressed (default = true)
	 * - closeButton  boolean  Display modal close button (default = true)
	 * - animation    boolean  Fade in from the top of the page (default = true)
	 * - footer       string   Optional markup for the modal footer
	 * - url          string   URL of a resource to be inserted as an `<iframe>` inside the modal body
	 * - height       string   height of the `<iframe>` containing the remote resource
	 * - width        string   width of the `<iframe>` containing the remote resource
	 */
	public static function renderModal($selector = 'modal', $params = [], $body = '')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return '';
		}

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'modal');

		$layoutData = [
			'selector' => $selector,
			'params'   => $params,
			'body'     => $body,
		];

		static::$loaded[__METHOD__][$selector] = true;

		return LayoutHelper::render('libraries.html.bootstrap.modal.main', $layoutData);
	}

	/**
	 * Add javascript support for Bootstrap popovers
	 *
	 * Use element's Title as popover content
	 *
	 * @param   string  $selector  Selector for the popover
	 * @param   array   $params    An array of options for the popover.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * - Options($params)  for the popover can be:
	 * - animation    boolean          apply a css fade transition to the popover
	 * - container    string|boolean   Appends the popover to a specific element: { container: 'body' }
	 * - content      string|function  default content value if `data-content` attribute isn't present
	 * - delay        number|object    delay showing and hiding the popover (ms) - does not apply to manual trigger type
	 *                                 If a number is supplied, delay is applied to both hide/show
	 *                                 Object structure is: delay: { show: 500, hide: 100 }
	 * - html         boolean          Insert HTML into the popover. If false, jQuery's text method will be used to insert
	 *                                 content into the dom.
	 * - placement    string|function  how to position the popover - top | bottom | left | right
	 * - selector     string           If a selector is provided, popover objects will be delegated to the specified targets.
	 * - template     string           Base HTML to use when creating the popover.
	 * - title        string|function  default title value if `title` tag isn't present
	 * - trigger      string           how popover is triggered - hover | focus | manual
	 * - constraints  array            An array of constraints - passed through to Popper.
	 * - offset       string           Offset of the popover relative to its target.
	 */
	public static function popover($selector = '[data-bs-toggle="popover"]', $params = [])
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		$opt['animation']   = isset($params['animation']) ? $params['animation'] : null;
		$opt['container']   = isset($params['container']) ? $params['container'] : 'body';
		$opt['content']     = isset($params['content']) ? $params['content'] : null;
		$opt['delay']       = isset($params['delay']) ? $params['delay'] : null;
		$opt['html']        = isset($params['html']) ? $params['html'] : true;
		$opt['placement']   = isset($params['placement']) ? $params['placement'] : null;

		// $opt['selector']    = isset($params['selector']) ? $params['selector'] : '.popover';
		$opt['template']    = isset($params['template']) ? $params['template'] : null;
		$opt['title']       = isset($params['title']) ? $params['title'] : null;
		$opt['trigger']     = isset($params['trigger']) ? $params['trigger'] : 'hover focus';

		// $opt['constraints'] = isset($params['constraints']) ? $params['constraints'] :
		//	['to' => 'scrollParent', 'attachment' => 'together', 'pin' => true];
		$opt['offset']      = isset($params['offset']) ? $params['offset'] : '0,0';

		$opt     = (object) array_filter((array) $opt);

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'popover');

		Factory::getDocument()->addScriptOptions('bootstrap.popover', [$selector => $opt]);

		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add javascript support for Bootstrap ScrollSpy
	 *
	 * @param   string  $selector  The ID selector for the ScrollSpy element.
	 * @param   array   $params    An array of options for the ScrollSpy.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * Options ($param) for the ScrollSpy can be:
	 * - offset  number  Pixels to offset from top when calculating position of scroll.
	 */
	public static function scrollspy($selector = 'navbar', $params = [])
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'scrollspy');

		Factory::getDocument()->addScriptOptions('bootstrap.scrollspy', [$selector => $params]);

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
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * Options ($params) for the tooltip can be:
	 * - animation    boolean          apply a css fade transition to the popover
	 * - container    string|boolean   Appends the popover to a specific element: { container: 'body' }
	 * - delay        number|object    delay showing and hiding the popover (ms) - does not apply to manual trigger type
	 *                                 If a number is supplied, delay is applied to both hide/show
	 *                                 Object structure is: delay: { show: 500, hide: 100 }
	 * - html         boolean          Insert HTML into the popover. If false, jQuery's text method will be used to
	 *                                 insert content into the dom.
	 * - placement    string|function  how to position the popover - top | bottom | left | right
	 * - selector     string           If a selector is provided, popover objects will be
	 *                                 delegated to the specified targets.
	 * - template     string           Base HTML to use when creating the popover.
	 * - title        string|function  default title value if `title` tag isn't present
	 * - trigger      string           how popover is triggered - hover | focus | manual
	 * - constraints  array            An array of constraints - passed through to Popper.
	 * - offset       string           Offset of the popover relative to its target.
	 */
	public static function tooltip($selector = '[data-bs-toggle=tooltip]', $params = [])
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'tooltip');

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

		// $opt['constraints'] = isset($params['constraints']) ? $params['constraints'] : ['to' => 'scrollParent', 'attachment' => 'together', 'pin' => true];
		$opt['offset']      = isset($params['offset']) ? $params['offset'] : '0,0';

		//		$onShow             = isset($params['onShow']) ? (string) $params['onShow'] : null;
		//		$onShown            = isset($params['onShown']) ? (string) $params['onShown'] : null;
		//		$onHide             = isset($params['onHide']) ? (string) $params['onHide'] : null;
		//		$onHidden           = isset($params['onHidden']) ? (string) $params['onHidden'] : null;

		$opt     = (object) array_filter((array) $opt);

		Factory::getDocument()->addScriptOptions('bootstrap.tooltip', [$selector => $opt]);

		// Set static array
		static::$loaded[__METHOD__][$selector] = true;
	}

	/**
	 * Add javascript support for Bootstrap accordions and insert the accordion
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
	 * @return  string  HTML for the accordion
	 *
	 * @since   3.0
	 */
	public static function startAccordion($selector = 'myAccordian', $params = [])
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return '';
		}

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'collapse');

		// Setup options object
		$opt['parent'] = isset($params['parent']) ? ($params['parent'] == true ? '#' . $selector : $params['parent']) : '';
		$opt['toggle'] = isset($params['toggle']) ? (boolean) $params['toggle'] : !($opt['parent'] === false || isset($params['active']));
		$opt['onShow'] = isset($params['onShow']) ? (string) $params['onShow'] : null;
		$opt['onShown'] = isset($params['onShown']) ? (string) $params['onShown'] : null;
		$opt['onHide'] = isset($params['onHide']) ? (string) $params['onHide'] : null;
		$opt['onHidden'] = isset($params['onHidden']) ? (string) $params['onHidden'] : null;
		$opt['active'] = isset($params['active']) ? (string) $params['active'] : '';

		Factory::getDocument()->addScriptOptions('bootstrap.accordion', [$selector => $opt]);

		static::$loaded[__METHOD__][$selector] = $opt;

		return '<div id="' . $selector . '" class="accordion" role="tablist">';
	}

	/**
	 * Close the current accordion
	 *
	 * @return  string  HTML to close the accordion
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
		$in        = static::$loaded[__CLASS__ . '::startAccordion'][$selector]['active'] === $id ? ' show' : '';
		$collapsed = static::$loaded[__CLASS__ . '::startAccordion'][$selector]['active'] === $id ? '' : ' collapsed';
		$parent    = static::$loaded[__CLASS__ . '::startAccordion'][$selector]['parent'] ?
			' data-bs-parent="' . static::$loaded[__CLASS__ . '::startAccordion'][$selector]['parent'] . '"' : '';
		$class     = (!empty($class)) ? ' ' . $class : '';

		$html = '<div class="card' . $class . '">'
			. '<a href="#' . $id . '" data-bs-toggle="collapse"' . $parent . ' class="card-header' . $collapsed . '" role="tab">'
			. $text
			. '</a>'
			. '<div class="collapse' . $in . '" id="' . $id . '" role="tabpanel">'
			. '<div class="card-body">';

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
	public static function startTabSet($selector = '.myTab', $params = [])
	{
		$sig = md5(serialize([$selector, $params]));

		if (!isset(static::$loaded[__METHOD__][$sig]))
		{
			// Include Bootstrap component
			HTMLHelper::_('bootstrap.loadScript', 'tab');

			// Setup options object
			$opt['active'] = (isset($params['active']) && ($params['active'])) ? (string) $params['active'] : '';

			Factory::getDocument()->addScriptOptions('bootstrap.tab', [$selector => $opt]);

			// Set static array
			static::$loaded[__METHOD__][$sig] = true;
			static::$loaded[__METHOD__][$selector]['active'] = $opt['active'];

			return LayoutHelper::render('libraries.html.bootstrap.tab.starttabset', ['selector' => $selector]);
		}
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
		return LayoutHelper::render('libraries.html.bootstrap.tab.endtabset');
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
		static $tabLayout = null;

		$tabLayout = $tabLayout === null ? new FileLayout('libraries.html.bootstrap.tab.addtab') : $tabLayout;
		$active = (static::$loaded[__CLASS__ . '::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

		return $tabLayout->render(['id' => str_replace('.', '', $id), 'active' => $active, 'title' => $title]);
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
		return LayoutHelper::render('libraries.html.bootstrap.tab.endtab');
	}

	/**
	 * Loads CSS files needed by Bootstrap
	 *
	 * @param   boolean  $includeMainCss  If true, main bootstrap.css files are loaded
	 * @param   string   $direction       rtl or ltr direction. If empty, ltr is assumed
	 * @param   array    $attribs         Optional array of attributes to be passed to HTMLHelper::_('stylesheet')
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadCss($includeMainCss = true, $direction = 'ltr', $attribs = [])
	{
		// Load Bootstrap main CSS
		if ($includeMainCss)
		{
			Factory::getDocument()->getWebAssetManager()->useStyle('bootstrap.css');
		}

		/**
		 * BOOTSTRAP RTL - WILL SORT OUT LATER DOWN THE LINE
		 * Load Bootstrap RTL CSS
		 * if ($direction === 'rtl')
		 * {
		 *  HTMLHelper::_('stylesheet', 'jui/bootstrap-rtl.css', ['version' => 'auto', 'relative' => true], $attribs);
		 * }
		 */
	}

	/**
	 * Add javascript support for Bootstrap toasts
	 *
	 * @param   string  $selector  Common class for the toasts
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function toast($selector = '.toast')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap component
		HTMLHelper::_('bootstrap.loadScript', 'toast');

		Factory::getDocument()->addScriptOptions('bootstrap.toast', [$selector]);

		static::$loaded[__METHOD__][$selector] = true;
	}
}
