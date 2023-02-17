<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Layout;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Version;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @link   https://docs.joomla.org/Special:MyLanguage/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since  3.0
 */
class FileLayout extends BaseLayout
{
    /**
     * Cached layout paths
     *
     * @var    array
     * @since  3.5
     */
    protected static $cache = [];

    /**
     * Dot separated path to the layout file, relative to base path
     *
     * @var    string
     * @since  3.0
     */
    protected $layoutId = '';

    /**
     * Base path to use when loading layout files
     *
     * @var    string
     * @since  3.0
     */
    protected $basePath = null;

    /**
     * Full path to actual layout files, after possible template override check
     *
     * @var    string
     * @since  3.0.3
     */
    protected $fullPath = null;

    /**
     * Paths to search for layouts
     *
     * @var    array
     * @since  3.2
     */
    protected $includePaths = [];

    /**
     * Method to instantiate the file-based layout.
     *
     * @param   string  $layoutId  Dot separated path to the layout file, relative to base path
     * @param   string  $basePath  Base path to use when loading layout files
     * @param   mixed   $options   Optional custom options to load. Registry or array format [@since 3.2]
     *
     * @since   3.0
     */
    public function __construct($layoutId, $basePath = null, $options = null)
    {
        // Initialise / Load options
        $this->setOptions($options);

        // Main properties
        $this->setLayoutId($layoutId);
        $this->basePath = $basePath;

        // Init Environment
        $this->setComponent($this->options->get('component', 'auto'));
        $this->setClient($this->options->get('client', 'auto'));
    }

    /**
     * Method to render the layout.
     *
     * @param   array  $displayData  Array of properties available for use inside the layout file to build the displayed output
     *
     * @return  string  The necessary HTML to display the layout
     *
     * @since   3.0
     */
    public function render($displayData = [])
    {
        $this->clearDebugMessages();

        // Inherit base output from parent class
        $layoutOutput = '';

        // Automatically merge any previously data set if $displayData is an array
        if (\is_array($displayData)) {
            $displayData = array_merge($this->data, $displayData);
        }

        // Check possible overrides, and build the full path to layout file
        $path = $this->getPath();

        if ($this->isDebugEnabled()) {
            echo '<pre>' . $this->renderDebugMessages() . '</pre>';
        }

        // Nothing to show
        if (empty($path)) {
            return $layoutOutput;
        }

        ob_start();
        include $path;
        $layoutOutput .= ob_get_contents();
        ob_end_clean();

        return $layoutOutput;
    }

    /**
     * Method to finds the full real file path, checking possible overrides
     *
     * @return  string  The full path to the layout file
     *
     * @since   3.0
     */
    protected function getPath()
    {
        $layoutId     = $this->getLayoutId();
        $includePaths = $this->getIncludePaths();
        $suffixes     = $this->getSuffixes();

        $this->addDebugMessage('<strong>Layout:</strong> ' . $this->layoutId);

        if (!$layoutId) {
            $this->addDebugMessage('<strong>There is no active layout</strong>');

            return;
        }

        if (!$includePaths) {
            $this->addDebugMessage('<strong>There are no folders to search for layouts:</strong> ' . $layoutId);

            return;
        }

        $hash = md5(
            json_encode(
                [
                    'paths'    => $includePaths,
                    'suffixes' => $suffixes,
                ]
            )
        );

        if (!empty(static::$cache[$layoutId][$hash])) {
            $this->addDebugMessage('<strong>Cached path:</strong> ' . static::$cache[$layoutId][$hash]);

            return static::$cache[$layoutId][$hash];
        }

        $this->addDebugMessage('<strong>Include Paths:</strong> ' . print_r($includePaths, true));

        // Search for suffixed versions. Example: tags.j31.php
        if ($suffixes) {
            $this->addDebugMessage('<strong>Suffixes:</strong> ' . print_r($suffixes, true));

            foreach ($suffixes as $suffix) {
                $rawPath  = str_replace('.', '/', $this->layoutId) . '.' . $suffix . '.php';
                $this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

                if ($foundLayout = Path::find($this->includePaths, $rawPath)) {
                    $this->addDebugMessage('<strong>Found layout:</strong> ' . $this->fullPath);

                    static::$cache[$layoutId][$hash] = $foundLayout;

                    return static::$cache[$layoutId][$hash];
                }
            }
        }

        // Standard version
        $rawPath  = str_replace('.', '/', $this->layoutId) . '.php';
        $this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

        $foundLayout = Path::find($this->includePaths, $rawPath);

        if (!$foundLayout) {
            $this->addDebugMessage('<strong>Unable to find layout: </strong> ' . $layoutId);

            return;
        }

        $this->addDebugMessage('<strong>Found layout:</strong> ' . $foundLayout);

        static::$cache[$layoutId][$hash] = $foundLayout;

        return static::$cache[$layoutId][$hash];
    }

    /**
     * Add one path to include in layout search. Proxy of addIncludePaths()
     *
     * @param   string|string[]  $path  The path to search for layouts
     *
     * @return  self
     *
     * @since   3.2
     */
    public function addIncludePath($path)
    {
        $this->addIncludePaths($path);

        return $this;
    }

    /**
     * Add one or more paths to include in layout search
     *
     * @param   string|string[]  $paths  The path or array of paths to search for layouts
     *
     * @return  self
     *
     * @since   3.2
     */
    public function addIncludePaths($paths)
    {
        if (empty($paths)) {
            return $this;
        }

        $includePaths = $this->getIncludePaths();

        if (\is_array($paths)) {
            $includePaths = array_unique(array_merge($paths, $includePaths));
        } else {
            array_unshift($includePaths, $paths);
        }

        $this->setIncludePaths($includePaths);

        return $this;
    }

    /**
     * Clear the include paths
     *
     * @return  self
     *
     * @since   3.5
     */
    public function clearIncludePaths()
    {
        $this->includePaths = [];

        return $this;
    }

    /**
     * Get the active include paths
     *
     * @return  array
     *
     * @since   3.5
     */
    public function getIncludePaths()
    {
        if (empty($this->includePaths)) {
            $this->includePaths = $this->getDefaultIncludePaths();
        }

        return $this->includePaths;
    }

    /**
     * Get the active layout id
     *
     * @return  string
     *
     * @since   3.5
     */
    public function getLayoutId()
    {
        return $this->layoutId;
    }

    /**
     * Get the active suffixes
     *
     * @return  array
     *
     * @since   3.5
     */
    public function getSuffixes()
    {
        return $this->getOptions()->get('suffixes', []);
    }

    /**
     * Load the automatically generated language suffixes.
     * Example: array('es-ES', 'es', 'ltr')
     *
     * @return  self
     *
     * @since   3.5
     */
    public function loadLanguageSuffixes()
    {
        $lang = Factory::getLanguage();

        $langTag = $lang->getTag();
        $langParts = explode('-', $langTag);

        $suffixes = [$langTag, $langParts[0]];
        $suffixes[] = $lang->isRtl() ? 'rtl' : 'ltr';

        $this->setSuffixes($suffixes);

        return $this;
    }

    /**
     * Load the automatically generated version suffixes.
     * Example: array('j311', 'j31', 'j3')
     *
     * @return  self
     *
     * @since   3.5
     */
    public function loadVersionSuffixes()
    {
        $cmsVersion = new Version();

        // Example j311
        $fullVersion = 'j' . str_replace('.', '', $cmsVersion->getShortVersion());

        // Create suffixes like array('j311', 'j31', 'j3')
        $suffixes = [
            $fullVersion,
            substr($fullVersion, 0, 3),
            substr($fullVersion, 0, 2),
        ];

        $this->setSuffixes(array_unique($suffixes));

        return $this;
    }

    /**
     * Remove one path from the layout search
     *
     * @param   string  $path  The path to remove from the layout search
     *
     * @return  self
     *
     * @since   3.2
     */
    public function removeIncludePath($path)
    {
        $this->removeIncludePaths($path);

        return $this;
    }

    /**
     * Remove one or more paths to exclude in layout search
     *
     * @param   string  $paths  The path or array of paths to remove for the layout search
     *
     * @return  self
     *
     * @since   3.2
     */
    public function removeIncludePaths($paths)
    {
        if (!empty($paths)) {
            $paths = (array) $paths;

            $this->includePaths = array_diff($this->includePaths, $paths);
        }

        return $this;
    }

    /**
     * Validate that the active component is valid
     *
     * @param   string  $option  URL Option of the component. Example: com_content
     *
     * @return  boolean
     *
     * @since   3.2
     */
    protected function validComponent($option = null)
    {
        // By default we will validate the active component
        $component = $option ?? $this->options->get('component', null);

        // Valid option format
        if (!empty($component) && substr_count($component, 'com_')) {
            // Latest check: component exists and is enabled
            return ComponentHelper::isEnabled($component);
        }

        return false;
    }

    /**
     * Method to change the component where search for layouts
     *
     * @param   string  $option  URL Option of the component. Example: com_content
     *
     * @return  mixed  Component option string | null for none
     *
     * @since   3.2
     */
    public function setComponent($option)
    {
        $component = null;

        switch ((string) $option) {
            case 'none':
                $component = null;
                break;

            case 'auto':
                $component = ApplicationHelper::getComponentName();
                break;

            default:
                $component = $option;
                break;
        }

        // Extra checks
        if (!$this->validComponent($component)) {
            $component = null;
        }

        $this->options->set('component', $component);

        // Refresh include paths
        $this->clearIncludePaths();
    }

    /**
     * Function to initialise the application client
     *
     * @param   mixed  $client  Frontend: 'site' or 0 | Backend: 'admin' or 1
     *
     * @return  void
     *
     * @since   3.2
     */
    public function setClient($client)
    {
        // Force string conversion to avoid unexpected states
        switch ((string) $client) {
            case 'site':
            case '0':
                $client = 0;
                break;

            case 'admin':
            case '1':
                $client = 1;
                break;

            default:
                $client = (int) Factory::getApplication()->isClient('administrator');
                break;
        }

        $this->options->set('client', $client);

        // Refresh include paths
        $this->clearIncludePaths();
    }

    /**
     * Set the active layout id
     *
     * @param   string  $layoutId  Layout identifier
     *
     * @return  self
     *
     * @since   3.5
     */
    public function setLayoutId($layoutId)
    {
        $this->layoutId = $layoutId;
        $this->fullPath = null;

        return $this;
    }

    /**
     * Get the default array of include paths
     *
     * @return  array
     *
     * @since   3.5
     */
    public function getDefaultIncludePaths()
    {
        // Get the template
        $app          = Factory::getApplication();
        $templateName = $this->options->get('template');

        if ($templateName) {
            // Check template name in the options
            $template = (object) [
                'template' => $templateName,
                'parent' => '',
            ];
        } elseif ($app->isClient('site') || $app->isClient('administrator')) {
            // Try to get a default template
            $template = $app->getTemplate(true);
        } else {
            // Template not found
            $template = false;
        }

        // Reset includePaths
        $paths = [];

        // (1 - highest priority) Received a custom high priority path
        if ($this->basePath !== null) {
            $paths[] = rtrim($this->basePath, DIRECTORY_SEPARATOR);
        }

        // Component layouts & overrides if exist
        $component = $this->options->get('component', null);

        if (!empty($component)) {
            if ($template) {
                // (2) Component template overrides path
                $paths[] = JPATH_THEMES . '/' . $template->template . '/html/layouts/' . $component;

                if (!empty($template->parent)) {
                    // (2.a) Component template overrides path for an inherited template using the parent
                    $paths[] = JPATH_THEMES . '/' . $template->parent . '/html/layouts/' . $component;
                }
            }

            // (3) Component path
            if ($this->options->get('client') == 0) {
                $paths[] = JPATH_SITE . '/components/' . $component . '/layouts';
            } else {
                $paths[] = JPATH_ADMINISTRATOR . '/components/' . $component . '/layouts';
            }
        }

        if ($template) {
            // (4) Standard Joomla! layouts overridden
            $paths[] = JPATH_THEMES . '/' . $template->template . '/html/layouts';

            if (!empty($template->parent)) {
                // (4.a) Component template overrides path for an inherited template using the parent
                $paths[] = JPATH_THEMES . '/' . $template->parent . '/html/layouts';
            }
        }

        // (5 - lower priority) Frontend base layouts
        $paths[] = JPATH_ROOT . '/layouts';

        return $paths;
    }

    /**
     * Set the include paths to search for layouts
     *
     * @param   array  $paths  Array with paths to search in
     *
     * @return  self
     *
     * @since   3.5
     */
    public function setIncludePaths($paths)
    {
        $this->includePaths = (array) $paths;

        return $this;
    }

    /**
     * Set suffixes to search layouts
     *
     * @param   mixed  $suffixes  String with a single suffix or 'auto' | 'none' or array of suffixes
     *
     * @return  self
     *
     * @since   3.5
     */
    public function setSuffixes(array $suffixes)
    {
        $this->options->set('suffixes', $suffixes);

        return $this;
    }

    /**
     * Render a layout with the same include paths & options
     *
     * @param   string  $layoutId     The identifier for the sublayout to be searched in a subfolder with the name of the current layout
     * @param   mixed   $displayData  Data to be rendered
     *
     * @return  string  The necessary HTML to display the layout
     *
     * @since   3.2
     */
    public function sublayout($layoutId, $displayData)
    {
        // Sublayouts are searched in a subfolder with the name of the current layout
        if (!empty($this->layoutId)) {
            $layoutId = $this->layoutId . '.' . $layoutId;
        }

        $sublayout = new static($layoutId, $this->basePath, $this->options);
        $sublayout->includePaths = $this->includePaths;

        return $sublayout->render($displayData);
    }
}
