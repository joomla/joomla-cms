<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	 *
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

			$options = JHtml::getJSObject($opt);

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
	 *
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
			$debug = (boolean) $config->get('debug');
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
			$opt['show'] = (isset($params['show']) && ($params['show'])) ? (boolean) $params['show'] : true;
			$opt['remote'] = (isset($params['remote']) && ($params['remote'])) ? (boolean) $params['remote'] : '';

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
	 * Method to render a Bootstrap modal
	 *
	 * @param   string  $selector  The ID selector for the modal.
	 * @param   array   $params    An array of options for the modal.
	 * @param   string  $footer    Optional markup for the modal footer
	 *
	 * @return  string  HTML markup for a modal
	 *
	 * @since   3.0
	 */
	public static function renderModal($selector = 'modal', $params = array(), $footer = '')
	{
		// Ensure the behavior is loaded
		self::modal($selector, $params);

		$html = "<div class=\"modal hide fade\" id=\"" . $selector . "\">\n";
		$html .= "<div class=\"modal-header\">\n";
		$html .= "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">×</button>\n";
		$html .= "<h3>" . $params['title'] . "</h3>\n";
		$html .= "</div>\n";
		$html .= "<div id=\"" . $selector . "-container\">\n";
		$html .= "</div>\n";
		$html .= "</div>\n";

		$html .= "<script>";
		$html .= "jQuery('#" . $selector . "').on('show', function () {\n";
		$html .= "document.getElementById('" . $selector . "-container').innerHTML = '<div class=\"modal-body\"><iframe class=\"iframe\" src=\""
			. $params['url'] . "\" height=\"" . $params['height'] . "\" width=\"" . $params['width'] . "\"></iframe></div>" . $footer . "';\n";
		$html .= "});\n";
		$html .= "</script>";

		return $html;
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
	public static function popover($selector = '.hasPopover', $params = array())
	{
		// Only load once
		if (isset(self::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		self::framework();

		$opt['animation'] = isset($params['animation']) ? $params['animation'] : null;
		$opt['html'] = isset($params['html']) ? $params['html'] : null;
		$opt['placement'] = isset($params['placement']) ? $params['placement'] : null;
		$opt['selector'] = isset($params['selector']) ? $params['selector'] : null;
		$opt['title'] = isset($params['title']) ? $params['title'] : null;
		$opt['trigger'] = isset($params['trigger']) ? $params['trigger'] : 'hover';
		$opt['content'] = isset($params['content']) ? $params['content'] : null;
		$opt['delay'] = isset($params['delay']) ? $params['delay'] : null;

		$options = JHtml::getJSObject($opt);

		// Attach the popover to the document
		JFactory::getDocument()->addScriptDeclaration(
			"jQuery(document).ready(function()
			{
				jQuery('" . $selector . "').popover(" . $options . ");
			});"
		);

		self::$loaded[__METHOD__][$selector] = true;

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
	public static function tooltip($selector = '.hasTooltip', $params = array())
	{
		if (!isset(self::$loaded[__METHOD__][$selector]))
		{
			// Include Bootstrap framework
			self::framework();

			// Setup options object
			$opt['animation'] = (isset($params['animation']) && ($params['animation'])) ? (boolean) $params['animation'] : null;
			$opt['html'] = (isset($params['html']) && ($params['html'])) ? (boolean) $params['html'] : null;
			$opt['placement'] = (isset($params['placement']) && ($params['placement'])) ? (string) $params['placement'] : null;
			$opt['selector'] = (isset($params['selector']) && ($params['selector'])) ? (string) $params['selector'] : null;
			$opt['title'] = (isset($params['title']) && ($params['title'])) ? (string) $params['title'] : null;
			$opt['trigger'] = (isset($params['trigger']) && ($params['trigger'])) ? (string) $params['trigger'] : null;
			$opt['delay'] = (isset($params['delay']) && ($params['delay'])) ? (int) $params['delay'] : null;

			$options = JHtml::getJSObject($opt);

			// Attach tooltips to document
			JFactory::getDocument()->addScriptDeclaration(
				"jQuery(document).ready(function()
				{
					jQuery('" . $selector . "').tooltip(" . $options . ");
				});"
			);

			// Set static array
			self::$loaded[__METHOD__][$selector] = true;
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
		$in = (self::$loaded['JHtmlBootstrap::startAccordion']['active'] == $id) ? ' in' : '';
		$class = (!empty($class)) ? ' ' . $class : '';

		$html = '<div class="accordion-group' . $class . '">'
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
	 * @since   3.1
	 */
	public static function startTabSet($selector = 'myTab', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (!isset(self::$loaded[__METHOD__][$sig]))
		{
			// Include Bootstrap framework
			self::framework();

			// Setup options object
			$opt['active'] = (isset($params['active']) && ($params['active'])) ? (string) $params['active'] : '';

			$options = JHtml::getJSObject($opt);

			// Attach tabs to document
			JFactory::getDocument()
				->addScriptDeclaration(JLayoutHelper::render('libraries.cms.html.bootstrap.starttabsetscript', array('selector' => $selector)));

			// Set static array
			self::$loaded[__METHOD__][$sig] = true;
			self::$loaded[__METHOD__][$selector]['active'] = $opt['active'];
		}

		$html = JLayoutHelper::render('libraries.cms.html.bootstrap.starttabset', array('selector' => $selector));

		return $html;
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
		$html = JLayoutHelper::render('libraries.cms.html.bootstrap.endtabset');

		return $html;
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

		$tabScriptLayout = is_null($tabScriptLayout) ? new JLayoutFile('libraries.cms.html.bootstrap.addtabscript') : $tabScriptLayout;
		$tabLayout = is_null($tabLayout) ? new JLayoutFile('libraries.cms.html.bootstrap.addtab') : $tabLayout;

		$active = (self::$loaded['JHtmlBootstrap::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

		// Inject tab into UL
		JFactory::getDocument()
		->addScriptDeclaration($tabScriptLayout->render(array('selector' => $selector,'id' => $id, 'active' => $active, 'title' => $title)));

		$html = $tabLayout->render(array('id' => $id, 'active' => $active));

		return $html;
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
		$html = JLayoutHelper::render('libraries.cms.html.bootstrap.endtab');

		return $html;
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
	 * @deprecated  4.0	Use JHtml::_('bootstrap.startTabSet') instead.
	 */
	public static function startPane($selector = 'myTab', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));
		if (!isset(self::$loaded['JHtmlBootstrap::startTabSet'][$sig]))
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
			self::$loaded['JHtmlBootstrap::startTabSet'][$sig] = true;
			self::$loaded['JHtmlBootstrap::startTabSet'][$selector]['active'] = $opt['active'];
		}

		return '<div class="tab-content" id="' . $selector . 'Content">';
	}

	/**
	 * Close the current tab pane
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   3.0
	 * @deprecated  4.0	Use JHtml::_('bootstrap.endTabSet') instead.
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
	 * @deprecated  4.0 Use JHtml::_('bootstrap.addTab') instead.
	 */
	public static function addPanel($selector, $id)
	{
		$active = (self::$loaded['JHtmlBootstrap::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

		return '<div id="' . $id . '" class="tab-pane' . $active . '">';
	}

	/**
	 * Close the current tab content panel
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   3.0
	 * @deprecated  4.0 Use JHtml::_('bootstrap.endTab') instead.
	 */
	public static function endPanel()
	{
		return '</div>';
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
			JHtml::_('stylesheet', 'jui/bootstrap.min.css', $attribs, true);
			JHtml::_('stylesheet', 'jui/bootstrap-responsive.min.css', $attribs, true);
			JHtml::_('stylesheet', 'jui/bootstrap-extended.css', $attribs, true);
		}

		// Load Bootstrap RTL CSS
		if ($direction === 'rtl')
		{
			JHtml::_('stylesheet', 'jui/bootstrap-rtl.css', $attribs, true);
		}
	}
}
