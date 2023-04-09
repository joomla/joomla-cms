<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

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
    public static function alert($selector = ''): void
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        $doc = Factory::getDocument();

        if ($selector !== '') {
            $scriptOptions = $doc->getScriptOptions('bootstrap.alert');
            $options       = [$selector];

            if (is_array($scriptOptions)) {
                $options = array_merge($scriptOptions, $options);
            }

            $doc->addScriptOptions('bootstrap.alert', $options, false);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.alert');

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
    public static function button($selector = ''): void
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        $doc           = Factory::getDocument();

        if ($selector !== '') {
            $scriptOptions = $doc->getScriptOptions('bootstrap.button');
            $options       = [$selector];

            if (is_array($scriptOptions)) {
                $options = array_merge($scriptOptions, $options);
            }

            $doc->addScriptOptions('bootstrap.button', $options, false);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.button');

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
     * - interval  number   5000   The amount of time to delay between automatically cycling an item.
     *                             If false, carousel will not automatically cycle.
     * - keyboard  boolean  true   Whether the carousel should react to keyboard events.
     * - pause     string|  hover  Pauses the cycling of the carousel on mouseenter and resumes the cycling
     *             boolean         of the carousel on mouseleave.
     * - slide     string|  false  Autoplays the carousel after the user manually cycles the first item.
     *             boolean         If "carousel", autoplays the carousel on load.
     */
    public static function carousel($selector = '', $params = []): void
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            // Setup options object
            $opt = [
                'interval' => (isset($params['interval']) ? (int) $params['interval'] : 5000),
                'keyboard' => (isset($params['keyboard']) ? (bool) $params['keyboard'] : true),
                'pause'    => (isset($params['pause']) ? $params['pause'] : 'hover'),
                'slide'    => (isset($params['slide']) ? (bool) $params['slide'] : false),
                'wrap'     => (isset($params['wrap']) ? (bool) $params['wrap'] : true),
                'touch'    => (isset($params['touch']) ? (bool) $params['touch'] : true),
            ];

            Factory::getDocument()->addScriptOptions('bootstrap.carousel', [$selector => (object) array_filter((array) $opt)]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.carousel');

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Add javascript support for Bootstrap collapse
     *
     * @param   string    $selector  Common class for the collapse
     * @param   string[]  $params    Additional parameters - see below
     *
     * @return  void
     *
     * @throws \Exception
     *
     * @since   4.0.0
     *
     * Options for the collapse can be:
     * - parent    string   false  If parent is provided, then all collapsible elements under the specified parent will
     *                             be closed when this collapsible item is shown.
     * - toggle    boolean  true   Toggles the collapsible element on invocation
     */
    public static function collapse($selector = '', $params = []): void
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            // Setup options object
            $opt           = [];
            $opt['parent'] = isset($params['parent']) ? $params['parent'] : false;
            $opt['toggle'] = isset($params['toggle']) ? (bool) $params['toggle'] : true;

            Factory::getDocument()->addScriptOptions('bootstrap.collapse', [$selector => (object) array_filter((array) $opt)]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.collapse');

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Add javascript support for Bootstrap dropdowns
     *
     * @param   string  $selector  Common class for the dropdowns
     * @param   array   $params    The options for the dropdowns
     *
     * @return  void
     *
     * @since   4.0.0
     *
     * Options for the collapse can be:
     * - flip       boolean  true          Allow Dropdown to flip in case of an overlapping on the reference element
     * - boundary   string   scrollParent  Overflow constraint boundary of the dropdown menu
     * - reference  string   toggle        Reference element of the dropdown menu. Accepts 'toggle' or 'parent'
     * - display    string   dynamic       By default, we use Popper for dynamic positioning. Disable this with static
     */
    public static function dropdown($selector = '', $params = []): void
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            // Setup options object
            $opt                 = [];
            $opt['flip']         = isset($params['flip']) ? $params['flip'] : true;
            $opt['boundary']     = isset($params['boundary']) ? $params['boundary'] : 'scrollParent';
            $opt['reference']    = isset($params['reference']) ? $params['reference'] : 'toggle';
            $opt['display']      = isset($params['display']) ? $params['display'] : 'dynamic';
            $opt['popperConfig'] = isset($params['popperConfig']) ? (bool) $params['popperConfig'] : true;

            Factory::getDocument()->addScriptOptions('bootstrap.dropdown', [$selector => (object) array_filter((array) $opt)]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.dropdown');

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Add javascript support for Bootstrap modal
     *
     * @param   string  $selector  The ID selector for the modal.
     * @param   array   $options   An array of options for the modal.
     *
     * @return  void
     *
     * @since   4.0.0
     *
     * Options for the modal can be:
     * - backdrop     string|  true  Includes a modal-backdrop element. Alternatively, specify static
     *                boolean         for a backdrop which doesn't close the modal on click.
     * - keyboard     boolean  true  Closes the modal when escape key is pressed
     * - focus        boolean  true  Closes the modal when escape key is pressed
     */
    public static function modal($selector = '', $options = []): void
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            // Setup options object
            $opt = [
                'backdrop' => (isset($options['backdrop']) ? $options['backdrop'] : false),
                'keyboard' => (isset($options['keyboard']) ? (bool) $options['keyboard'] : true),
                'focus'    => (isset($options['focus']) ? (bool) $options['focus'] : true),
            ];

            Factory::getDocument()->addScriptOptions('bootstrap.modal', [$selector => (object) array_filter((array) $opt)]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.modal');

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Add javascript support for Bootstrap offcanvas
     *
     * @param   string  $selector  The ID selector for the offcanvas.
     * @param   array   $options   An array of options for the offcanvas.
     *
     * @return  void
     *
     * @since   4.0.0
     *
     * Options for the offcanvas can be:
     * - backdrop     boolean  true   Apply a backdrop on body while offcanvas is open
     * - keyboard     boolean  true   Closes the offcanvas when escape key is pressed
     * - scroll       boolean  false  Allow body scrolling while offcanvas is open
     */
    public static function offcanvas($selector = '', $options = []): void
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            // Setup options object
            $opt = [
                'backdrop' => (isset($options['backdrop']) ? (bool) $options['backdrop'] : true),
                'keyboard' => (isset($options['keyboard']) ? (bool) $options['keyboard'] : true),
                'scroll'   => (isset($options['scroll']) ? (bool) $options['scroll'] : false),
            ];

            Factory::getDocument()->addScriptOptions('bootstrap.offcanvas', [$selector => (object) array_filter((array) $opt)]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.offcanvas');

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Add javascript support for Bootstrap popovers
     *
     * Use element's Title as popover content
     *
     * @param   string  $selector  Selector for the popover
     * @param   array   $options   The options for the popover
     *
     * @return  void
     *
     * @since   3.0
     *
     * - Options for the popover can be:
     * - animation    boolean  true   Apply a CSS fade transition to the popover
     * - container    string|  false  Appends the popover to a specific element. Eg.: 'body'
     *                boolean
     * - content      string   null   Default content value if data-bs-content attribute isn't present
     * - delay        number   0      Delay showing and hiding the popover (ms)
     *                                 does not apply to manual trigger type
     * - html         boolean  true   Insert HTML into the popover. If false, innerText property will be used
     *                                 to insert content into the DOM.
     * - placement    string   right  How to position the popover - auto | top | bottom | left | right.
     *                                 When auto is specified, it will dynamically reorient the popover
     * - selector     string   false  If a selector is provided, popover objects will be delegated to the
     *                                 specified targets.
     * - template     string   null   Base HTML to use when creating the popover.
     * - title        string   null   Default title value if `title` tag isn't present
     * - trigger      string   click  How popover is triggered - click | hover | focus | manual
     * - offset       integer  0      Offset of the popover relative to its target.
     */
    public static function popover($selector = '', $options = []): void
    {
        // Only load once
        if (isset(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            // Setup options object
            $opt = [
                'animation'         => isset($options['animation']) ? (bool) $options['animation'] : true,
                'container'         => isset($options['container']) ? $options['container'] : 'body',
                'content'           => isset($options['content']) ? $options['content'] : null,
                'delay'             => isset($options['delay']) ? (int) $options['delay'] : ['show' => 50, 'hide' => 200],
                'html'              => isset($options['html']) ? (bool) $options['html'] : true,
                'placement'         => isset($options['placement']) ? $options['placement'] : null,
                'selector'          => isset($options['selector']) ? $options['selector'] : false,
                'template'          => isset($options['template']) ? $options['template'] : null,
                'title'             => isset($options['title']) ? $options['title'] : null,
                'trigger'           => isset($options['trigger']) ? $options['trigger'] : 'click',
                'offset'            => isset($options['offset']) ? $options['offset'] : [0, 10],
                'fallbackPlacement' => isset($options['fallbackPlacement']) ? $options['fallbackPlacement'] : null,
                'boundary'          => isset($options['boundary']) ? $options['boundary'] : 'scrollParent',
                'customClass'       => isset($options['customClass']) ? $options['customClass'] : null,
                'sanitize'          => isset($options['sanitize']) ? (bool) $options['sanitize'] : null,
                'allowList'         => isset($options['allowList']) ? $options['allowList'] : null,
            ];

            Factory::getDocument()->addScriptOptions('bootstrap.popover', [$selector => (object) array_filter((array) $opt)]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.popover');

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Add javascript support for Bootstrap Scrollspy
     *
     * @param   string  $selector  The ID selector for the ScrollSpy element.
     * @param   array   $options   An array of options for the ScrollSpy.
     *
     * @return  void
     *
     * @since   3.0
     *
     * Options for the Scrollspy can be:
     * - offset  number  Pixels to offset from top when calculating position of scroll.
     * - method  string  Finds which section the spied element is in.
     * - target  string  Specifies element to apply Scrollspy plugin.
     */
    public static function scrollspy($selector = '', $options = []): void
    {
        // Only load once
        if (isset(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            // Setup options object
            $opt = [
                'offset' => isset($options['offset']) ? (int) $options['offset'] : 10,
                'method' => isset($options['method']) ? $options['method'] : 'auto',
                'target' => isset($options['target']) ? $options['target'] : null,
            ];

            Factory::getDocument()->addScriptOptions('bootstrap.scrollspy', [$selector => (object) array_filter((array) $opt)]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.scrollspy');

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Add javascript support for Bootstrap tab
     *
     * @param   string  $selector  Common class for the tabs
     * @param   array   $options   Options for the tabs
     *
     * @return  void
     *
     * @throws \Exception
     *
     * @since   4.0.0
     */
    public static function tab($selector = '', $options = []): void
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            Factory::getDocument()->addScriptOptions('bootstrap.tabs', [$selector => (object) $options]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.tab');

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Add javascript support for Bootstrap tooltips
     *
     * Add a title attribute to any element in the form
     * title="title::text"
     *
     * @param   string  $selector  The ID selector for the tooltip.
     * @param   array   $options   An array of options for the tooltip.
     *
     * @return  void
     *
     * @since   3.0
     *
     *                             Options for the tooltip can be:
     * - animation    boolean          apply a css fade transition to the popover
     * - container    string|boolean   Appends the popover to a specific element: { container: 'body' }
     * - delay        number|object    delay showing and hiding the popover (ms) - does not apply to manual trigger type
     *                                                              If a number is supplied, delay is applied to both hide/show
     *                                                              Object structure is: delay: { show: 500, hide: 100 }
     * - html         boolean          Insert HTML into the popover. If false, jQuery's text method will be used to
     *                                 insert content into the dom.
     * - placement    string|function  how to position the popover - top | bottom | left | right
     * - selector     string           If a selector is provided, popover objects will be
     *                                                              delegated to the specified targets.
     * - template     string           Base HTML to use when creating the popover.
     * - title        string|function  default title value if `title` tag isn't present
     * - trigger      string           how popover is triggered - hover | focus | manual
     * - constraints  array            An array of constraints - passed through to Popper.
     * - offset       string           Offset of the popover relative to its target.
     */
    public static function tooltip($selector = '', $options = []): void
    {
        // Only load once
        if (isset(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            // Setup options object
            $opt = [
                'animation'         => isset($options['animation']) ? (bool) $options['animation'] : true,
                'container'         => isset($options['container']) ? $options['container'] : 'body',
                'delay'             => isset($options['delay']) ? (int) $options['delay'] : 0,
                'html'              => isset($options['html']) ? (bool) $options['html'] : true,
                'placement'         => isset($options['placement']) ? $options['placement'] : null,
                'selector'          => isset($options['selector']) ? $options['selector'] : false,
                'template'          => isset($options['template']) ? $options['template'] : null,
                'title'             => isset($options['title']) ? $options['title'] : null,
                'trigger'           => isset($options['trigger']) ? $options['trigger'] : 'hover focus',
                'fallbackPlacement' => isset($options['fallbackPlacement']) ? $options['fallbackPlacement'] : null,
                'boundary'          => isset($options['boundary']) ? $options['boundary'] : 'clippingParents',
                'customClass'       => isset($options['customClass']) ? $options['customClass'] : null,
                'sanitize'          => isset($options['sanitize']) ? (bool) $options['sanitize'] : true,
                'allowList'         => isset($options['allowList']) ? $options['allowList'] : null,
            ];

            Factory::getDocument()->addScriptOptions('bootstrap.tooltip', [$selector => (object) array_filter((array) $opt)]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.popover');

        // Set static array
        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Add javascript support for Bootstrap toasts
     *
     * @param   string  $selector  Common class for the toasts
     * @param   array   $options   Options for the toasts
     *
     * @return  void
     *
     * @throws \Exception
     *
     * @since   4.0.0
     */
    public static function toast($selector = '', $options = []): void
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        if ($selector !== '') {
            // Setup options object
            $opt = [
                'animation' => isset($options['animation']) ? (string) $options['animation'] : null,
                'autohide'  => isset($options['autohide']) ? (bool) $options['autohide'] : true,
                'delay'     => isset($options['delay']) ? (int) $options['delay'] : 5000,
            ];

            Factory::getDocument()->addScriptOptions('bootstrap.toast', [$selector => (object) array_filter((array) $opt)]);
        }

        // Include the Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.toast');

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Method to load the ALL the Bootstrap Components
     *
     * If debugging mode is on an uncompressed version of Bootstrap is included for easier debugging.
     *
     * @param   mixed  $debug  Is debugging mode on? [optional]
     *
     * @return  void
     *
     * @since   3.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Will be removed without replacement
     *              Load the different scripts with their individual method calls
     */
    public static function framework($debug = null): void
    {
        $wa = Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager();

        array_map(
            function ($script) use ($wa) {
                $wa->useScript('bootstrap.' . $script);
            },
            ['alert', 'button', 'carousel', 'collapse', 'dropdown', 'modal', 'offcanvas', 'popover', 'scrollspy', 'tab', 'toast']
        );
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
    public static function loadCss($includeMainCss = true, $direction = 'ltr', $attribs = []): void
    {
        // Load Bootstrap main CSS
        if ($includeMainCss) {
            Factory::getDocument()->getWebAssetManager()->useStyle('bootstrap.css');
        }
    }

    /**
     * Add javascript support for Bootstrap accordions and insert the accordion
     *
     * @param   string  $selector  The ID selector for the tooltip. Expects a valid ID without the #!
     * @param   array   $options   An array of options for the tooltip.
     *
     * @return  string  HTML for the accordion
     *
     * @since   3.0
     *
     * Options for the tooltip can be:
     * - parent  selector  If selector then all collapsible elements under the specified parent will be closed when this
     *                     collapsible item is shown. (similar to traditional accordion behavior)
     * - toggle  boolean   Toggles the collapsible element on invocation
     * - active  string    Sets the active slide during load
     */
    public static function startAccordion($selector = 'myAccordian', $options = []): string
    {
        // Only load once
        if (isset(static::$loaded[__METHOD__][$selector])) {
            return '';
        }

        // Include Bootstrap component
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->useScript('bootstrap.collapse');

        // Setup options object
        $opt           = [];
        $opt['parent'] = isset($options['parent']) ?
            ($options['parent'] == true ? '#' . preg_replace('/^[\.#]/', '', $selector) : $options['parent']) : '';
        $opt['toggle'] = isset($options['toggle']) ? (bool) $options['toggle'] : !($opt['parent'] === false || isset($options['active']));
        $opt['active'] = isset($options['active']) ? (string) $options['active'] : '';

        // Initialise with the Joomla specifics
        $opt['isJoomla'] = true;

        Factory::getDocument()->addScriptOptions('bootstrap.accordion', ['#' . preg_replace('/^[\.#]/', '', $selector) => (object) array_filter((array) $opt)]);

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
    public static function endAccordion(): string
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
    public static function addSlide($selector, $text, $id, $class = ''): string
    {
        $in        = static::$loaded[__CLASS__ . '::startAccordion'][$selector]['active'] === $id ? ' show' : '';
        $collapsed = static::$loaded[__CLASS__ . '::startAccordion'][$selector]['active'] === $id ? '' : ' collapsed';
        $parent    = static::$loaded[__CLASS__ . '::startAccordion'][$selector]['parent'] ?
            'data-bs-parent="' . static::$loaded[__CLASS__ . '::startAccordion'][$selector]['parent'] . '"' : '';
        $class        = (!empty($class)) ? ' ' . $class : '';
        $ariaExpanded = $in === 'show' ? true : false;

        return <<<HTMLSTR
<div class="accordion-item $class">
  <h2 class="accordion-header" id="$id-heading">
    <button class="accordion-button $collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#$id" aria-expanded="$ariaExpanded" aria-controls="$id" role="tab">
		$text
    </button>
  </h2>
  <div id="$id" class="accordion-collapse collapse $in" aria-labelledby="$id-heading" $parent role="tabpanel">
    <div class="accordion-body">
HTMLSTR;
    }

    /**
     * Close the current slide
     *
     * @return  string  HTML to close the slide
     *
     * @since   3.0
     */
    public static function endSlide(): string
    {
        return <<<HTMLSTR
		</div>
	</div>
</div>
HTMLSTR;
    }

    /**
     * Method to render a Bootstrap modal
     *
     * @param   string  $selector  The ID selector for the modal. Expects a valid ID without the #!
     * @param   array   $options   An array of options for the modal.
     * @param   string  $body      Markup for the modal body. Appended after the `<iframe>` if the URL option is set
     *
     * @return  string  HTML markup for a modal
     *
     * @since   3.0
     *
     * Options for the modal can be:
     * - backdrop     string|  true   Includes a modal-backdrop element. Alternatively, specify static
     *                boolean          for a backdrop which doesn't close the modal on click.
     * - keyboard     boolean  true   Closes the modal when escape key is pressed
     * - focus        boolean  true   Closes the modal when escape key is pressed
     * - title        string   null   The modal title
     * - closeButton  boolean  true   Display modal close button (default = true)
     * - footer       string   null   Optional markup for the modal footer
     * - url          string   null   URL of a resource to be inserted as an `<iframe>` inside the modal body
     * - height       string   null   Height of the `<iframe>` containing the remote resource
     * - width        string   null   Width of the `<iframe>` containing the remote resource
     */
    public static function renderModal($selector = 'modal', $options = [], $body = ''): string
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__][$selector])) {
            return '';
        }

        // Initialise with the Joomla specifics
        $options['isJoomla'] = true;

        // Include Basic Bootstrap component
        HTMLHelper::_('bootstrap.modal', '#' . preg_replace('/^[\.#]/', '', $selector), $options);

        $layoutData = [
            'selector' => $selector,
            'params'   => $options,
            'body'     => $body,
        ];

        static::$loaded[__METHOD__][$selector] = true;

        return LayoutHelper::render('libraries.html.bootstrap.modal.main', $layoutData);
    }

    /**
     * Creates a tab pane
     *
     * @param   string  $selector  The pane identifier. Expects a valid ID without the #!
     * @param   array   $params    The parameters for the pane
     *
     * @return  string
     *
     * @since   3.1
     */
    public static function startTabSet($selector = 'myTab', $params = []): string
    {
        $sig = md5(serialize([$selector, $params]));

        if (!isset(static::$loaded[__METHOD__][$sig])) {
            // Setup options object
            $opt           = [];
            $opt['active'] = (isset($params['active']) && ($params['active'])) ? (string) $params['active'] : '';

            // Initialise with the Joomla specifics
            $opt['isJoomla'] = true;

            // Include the Bootstrap Tab Component
            HTMLHelper::_('bootstrap.tab', '#' . preg_replace('/^[\.#]/', '', $selector), $opt);

            // Set static array
            static::$loaded[__METHOD__][$sig]                = true;
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
    public static function endTabSet(): string
    {
        return LayoutHelper::render('libraries.html.bootstrap.tab.endtabset');
    }

    /**
     * Begins the display of a new tab content panel.
     *
     * @param   string  $selector  Identifier of the panel. Expects a valid ID without the #!
     * @param   string  $id        The ID of the div element. Expects a valid ID without the #!
     * @param   string  $title     The title text for the new UL tab
     *
     * @return  string  HTML to start a new panel
     *
     * @since   3.1
     */
    public static function addTab($selector, $id, $title): string
    {
        static $tabLayout = null;

        $tabLayout = $tabLayout === null ? new FileLayout('libraries.html.bootstrap.tab.addtab') : $tabLayout;
        $active    = (static::$loaded[__CLASS__ . '::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

        return $tabLayout->render(['id' => preg_replace('/^[\.#]/', '', $id), 'active' => $active, 'title' => $title]);
    }

    /**
     * Close the current tab content panel
     *
     * @return  string  HTML to close the pane
     *
     * @since   3.1
     */
    public static function endTab(): string
    {
        return LayoutHelper::render('libraries.html.bootstrap.tab.endtab');
    }
}
