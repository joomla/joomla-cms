<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for the core UI elements.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JHtmlCoreui
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $loaded = array();

	/**
	 * Add javascript support for core UI alerts
	 *
	 * @param   array  $options  The parameters to built an alert
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function alert($options)
	{
		// Include the custom element
		JHtml::_('webcomponent', ['joomla-alert' => 'system/webcomponents/joomla-alert.min.js'], ['relative' => true, 'version' => 'auto']);
	}


	/**
	 * Method to render a core UI modal
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function renderModal($selector = 'modal', $params = array(), $body = '')
	{
	}

	/**
	 * Add javascript support for core UI popovers
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function popover($selector = '.hasPopover', $params = array())
	{
	}

	/**
	 * Add javascript support for core UI tooltips
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function tooltip($selector = '.hasTooltip', $params = array())
	{
	}

	/**
	 * Add javascript support for core UI accordians and insert the accordian
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function startAccordion($selector = 'myAccordian', $params = array())
	{
	}

	/**
	 * Close the current accordion
	 *
	 * @return  string  HTML to close the accordian
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function endAccordion()
	{
		return '</joomla-accordion>';
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function addSlide($selector, $text, $id, $class = '')
	{
	}

	/**
	 * Close the current slide
	 *
	 * @return  string  HTML to close the slide
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function endSlide()
	{
	}

	/**
	 * Creates a core UI tab pane
	 *
	 * @param   string  $selector  The pane identifier.
	 * @param   array   $params    The parameters for the pane
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function startTabSet($selector = 'myTab', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (!isset(static::$loaded[__METHOD__][$sig]))
		{
			// Include the custom element
			JHtml::_('webcomponent', ['joomla-tab' => 'system/joomla-tab.min.js'], ['relative' => true, 'version' => 'auto']);

			// Setup options object
			$opt['active'] = (isset($params['active']) && ($params['active'])) ? (string) $params['active'] : '';

			JFactory::getDocument()->addScriptOptions('bootstrap.tabs', array($selector => $opt));

			// Set static array
			static::$loaded[__METHOD__][$sig] = true;
			static::$loaded[__METHOD__][$selector]['active'] = $opt['active'];
		}

		return '<joomla-tab id="' . $selector . '">';
	}

	/**
	 * Close the current tab pane
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function endTabSet()
	{
		return '</joomla-tab>';
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function addTab($selector, $id, $title)
	{
		$active = (static::$loaded['JHtmlCoreui::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

		return '<section id="' . $id . '" class="' . $active . '" name="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '">';

	}

	/**
	 * Close the current tab content panel
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function endTab()
	{
		return '</section>';
	}
}
