<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla Html View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  2.5.5
 */
class HtmlView extends AbstractView implements CurrentUserInterface
{
    use CurrentUserTrait;

    /**
     * The base path of the view
     *
     * @var    string
     * @since  3.0
     */
    protected $_basePath = null;

    /**
     * Layout name
     *
     * @var    string
     * @since  3.0
     */
    protected $_layout = 'default';

    /**
     * Layout extension
     *
     * @var    string
     * @since  3.0
     */
    protected $_layoutExt = 'php';

    /**
     * Layout template
     *
     * @var    string
     * @since  3.0
     */
    protected $_layoutTemplate = '_';

    /**
     * The set of search directories for resources (templates)
     *
     * @var    array
     * @since  3.0
     */
    protected $_path = ['template' => [], 'helper' => []];

    /**
     * The name of the default template source file.
     *
     * @var    string
     * @since  3.0
     */
    protected $_template = null;

    /**
     * The output of the template script.
     *
     * @var    string
     * @since  3.0
     */
    protected $_output = null;

    /**
     * Charset to use in escaping mechanisms; defaults to urf8 (UTF-8)
     *
     * @var    string
     * @since  3.0
     */
    protected $_charset = 'UTF-8';

    /**
     * Constructor
     *
     * @param   array  $config  A named configuration array for object construction.
     *                          name: the name (optional) of the view (defaults to the view class name suffix).
     *                          charset: the character set to use for display
     *                          escape: the name (optional) of the function to use for escaping strings
     *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)
     *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name
     *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)
     *                          layout: the layout (optional) to use to display the view
     *
     * @since   3.0
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Set the charset (used by the variable escaping functions)
        if (\array_key_exists('charset', $config)) {
            @trigger_error(
                'Setting a custom charset for escaping is deprecated. Override \JViewLegacy::escape() instead.',
                E_USER_DEPRECATED
            );
            $this->_charset = $config['charset'];
        }

        // Set a base path for use by the view
        if (\array_key_exists('base_path', $config)) {
            $this->_basePath = $config['base_path'];
        } else {
            $this->_basePath = JPATH_COMPONENT;
        }

        // Set the default template search path
        if (\array_key_exists('template_path', $config)) {
            // User-defined dirs
            $this->_setPath('template', $config['template_path']);
        } elseif (is_dir($this->_basePath . '/tmpl/' . $this->getName())) {
            $this->_setPath('template', $this->_basePath . '/tmpl/' . $this->getName());
        } elseif (is_dir($this->_basePath . '/View/' . $this->getName() . '/tmpl')) {
            $this->_setPath('template', $this->_basePath . '/View/' . $this->getName() . '/tmpl');
        } elseif (is_dir($this->_basePath . '/view/' . $this->getName() . '/tmpl')) {
            $this->_setPath('template', $this->_basePath . '/view/' . $this->getName() . '/tmpl');
        } elseif (is_dir($this->_basePath . '/views/' . $this->getName() . '/tmpl')) {
            $this->_setPath('template', $this->_basePath . '/views/' . $this->getName() . '/tmpl');
        } else {
            $this->_setPath('template', $this->_basePath . '/views/' . $this->getName());
        }

        // Set the default helper search path
        if (\array_key_exists('helper_path', $config)) {
            // User-defined dirs
            $this->_setPath('helper', $config['helper_path']);
        } else {
            $this->_setPath('helper', $this->_basePath . '/helpers');
        }

        // Set the layout
        if (\array_key_exists('layout', $config)) {
            $this->setLayout($config['layout']);
        } else {
            $this->setLayout('default');
        }

        $this->baseurl = Uri::base(true);
    }

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws  \Exception
     * @see     \JViewLegacy::loadTemplate()
     * @since   3.0
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();

        if ($this->option) {
            $component = $this->option;
        } else {
            $component = ApplicationHelper::getComponentName();
        }

        $context = $component . '.' . $this->getName();

        $app->getDispatcher()->dispatch(
            'onBeforeDisplay',
            AbstractEvent::create(
                'onBeforeDisplay',
                [
                    'eventClass' => 'Joomla\CMS\Event\View\DisplayEvent',
                    'subject'    => $this,
                    'extension'  => $context
                ]
            )
        );

        $result = $this->loadTemplate($tpl);

        $eventResult = $app->getDispatcher()->dispatch(
            'onAfterDisplay',
            AbstractEvent::create(
                'onAfterDisplay',
                [
                    'eventClass' => 'Joomla\CMS\Event\View\DisplayEvent',
                    'subject'    => $this,
                    'extension'  => $context,
                    'source'     => $result
                ]
            )
        );

        $eventResult->getArgument('used', false);

        echo $result;
    }

    /**
     * Escapes a value for output in a view script.
     *
     * If escaping mechanism is htmlspecialchars, use
     * {@link $_charset} setting.
     *
     * @param   mixed  $var  The output to escape.
     *
     * @return  mixed  The escaped value.
     *
     * @note the ENT_COMPAT flag was replaced by ENT_QUOTES in Joomla 4.0 to also escape single quotes
     *
     * @since   3.0
     */
    public function escape($var)
    {
        if ($var === null) {
            return '';
        }

        return htmlspecialchars($var, ENT_QUOTES, $this->_charset);
    }

    /**
     * Get the layout.
     *
     * @return  string  The layout name
     *
     * @since   3.0
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Get the layout template.
     *
     * @return  string  The layout template name
     *
     * @since   3.0
     */
    public function getLayoutTemplate()
    {
        return $this->_layoutTemplate;
    }

    /**
     * Sets the layout name to use
     *
     * @param   string  $layout  The layout name or a string in format <template>:<layout file>
     *
     * @return  string  Previous value.
     *
     * @since   3.0
     */
    public function setLayout($layout)
    {
        $previous = $this->_layout;

        if (strpos($layout, ':') === false) {
            $this->_layout = $layout;
        } else {
            // Convert parameter to array based on :
            $temp = explode(':', $layout);
            $this->_layout = $temp[1];

            // Set layout template
            $this->_layoutTemplate = $temp[0];
        }

        return $previous;
    }

    /**
     * Allows a different extension for the layout files to be used
     *
     * @param   string  $value  The extension.
     *
     * @return  string  Previous value
     *
     * @since   3.0
     */
    public function setLayoutExt($value)
    {
        $previous = $this->_layoutExt;

        if ($value = preg_replace('#[^A-Za-z0-9]#', '', trim($value))) {
            $this->_layoutExt = $value;
        }

        return $previous;
    }

    /**
     * Adds to the stack of view script paths in LIFO order.
     *
     * @param   mixed  $path  A directory path or an array of paths.
     *
     * @return  void
     *
     * @since   3.0
     */
    public function addTemplatePath($path)
    {
        $this->_addPath('template', $path);
    }

    /**
     * Adds to the stack of helper script paths in LIFO order.
     *
     * @param   mixed  $path  A directory path or an array of paths.
     *
     * @return  void
     *
     * @since   3.0
     */
    public function addHelperPath($path)
    {
        $this->_addPath('helper', $path);
    }

    /**
     * Load a template file -- first look in the templates folder for an override
     *
     * @param   string  $tpl  The name of the template source file; automatically searches the template paths and compiles as needed.
     *
     * @return  string  The output of the the template script.
     *
     * @since   3.0
     * @throws  \Exception
     */
    public function loadTemplate($tpl = null)
    {
        // Clear prior output
        $this->_output = null;

        $template = Factory::getApplication()->getTemplate(true);
        $layout = $this->getLayout();
        $layoutTemplate = $this->getLayoutTemplate();

        // Create the template file name based on the layout
        $file = isset($tpl) ? $layout . '_' . $tpl : $layout;

        // Clean the file name
        $file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
        $tpl = isset($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

        // Load the language file for the template
        $lang = Factory::getLanguage();
        $lang->load('tpl_' . $template->template, JPATH_BASE)
            || $lang->load('tpl_' . $template->parent, JPATH_THEMES . '/' . $template->parent)
            || $lang->load('tpl_' . $template->template, JPATH_THEMES . '/' . $template->template);

        // Change the template folder if alternative layout is in different template
        if (isset($layoutTemplate) && $layoutTemplate !== '_' && $layoutTemplate != $template->template) {
            $this->_path['template'] = str_replace(
                JPATH_THEMES . DIRECTORY_SEPARATOR . $template->template,
                JPATH_THEMES . DIRECTORY_SEPARATOR . $layoutTemplate,
                $this->_path['template']
            );
        }

        // Load the template script
        $filetofind = $this->_createFileName('template', ['name' => $file]);
        $this->_template = Path::find($this->_path['template'], $filetofind);

        // If alternate layout can't be found, fall back to default layout
        if ($this->_template == false) {
            $filetofind = $this->_createFileName('', ['name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)]);
            $this->_template = Path::find($this->_path['template'], $filetofind);
        }

        if ($this->_template != false) {
            // Unset so as not to introduce into template scope
            unset($tpl, $file);

            // Never allow a 'this' property
            if (isset($this->this)) {
                unset($this->this);
            }

            // Start capturing output into a buffer
            ob_start();

            // Include the requested template filename in the local scope
            // (this will execute the view logic).
            include $this->_template;

            // Done with the requested template; get the buffer and
            // clear it.
            $this->_output = ob_get_contents();
            ob_end_clean();

            return $this->_output;
        }

        throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
    }

    /**
     * Load a helper file
     *
     * @param   string  $hlp  The name of the helper source file automatically searches the helper paths and compiles as needed.
     *
     * @return  void
     *
     * @since   3.0
     */
    public function loadHelper($hlp = null)
    {
        // Clean the file name
        $file = preg_replace('/[^A-Z0-9_\.-]/i', '', $hlp);

        // Load the template script
        $helper = Path::find($this->_path['helper'], $this->_createFileName('helper', ['name' => $file]));

        if ($helper != false) {
            // Include the requested template filename in the local scope
            include_once $helper;
        }
    }

    /**
     * Sets an entire array of search paths for templates or resources.
     *
     * @param   string  $type  The type of path to set, typically 'template'.
     * @param   mixed   $path  The new search path, or an array of search paths.  If null or false, resets to the current directory only.
     *
     * @return  void
     *
     * @since   3.0
     */
    protected function _setPath($type, $path)
    {
        if ($this->option) {
            $component = $this->option;
        } else {
            $component = ApplicationHelper::getComponentName();
        }

        $app = Factory::getApplication();

        // Clear out the prior search dirs
        $this->_path[$type] = [];

        // Actually add the user-specified directories
        $this->_addPath($type, $path);

        // Get the active template object
        $template = $app->getTemplate(true);

        // Always add the fallback directories as last resort
        switch (strtolower($type)) {
            case 'template':
                // Set the alternative template search dir
                if (isset($app)) {
                    if ($component) {
                        $component = preg_replace('/[^A-Z0-9_\.-]/i', '', $component);
                    }

                    $name = $this->getName();

                    if (!empty($template->parent)) {
                        // Parent template's overrides
                        $this->_addPath('template', JPATH_THEMES . '/' . $template->parent . '/html/' . $component . '/' . $name);

                        // Child template's overrides
                        $this->_addPath('template', JPATH_THEMES . '/' . $template->template . '/html/' . $component . '/' . $name);

                        break;
                    }

                    $this->_addPath('template', JPATH_THEMES . '/' . $template->template . '/html/' . $component . '/' . $name);
                }
                break;
        }
    }

    /**
     * Adds to the search path for templates and resources.
     *
     * @param   string  $type  The type of path to add.
     * @param   mixed   $path  The directory or stream, or an array of either, to search.
     *
     * @return  void
     *
     * @since   3.0
     */
    protected function _addPath($type, $path)
    {
        // Loop through the path directories
        foreach ((array) $path as $dir) {
            // Clean up the path
            $dir = Path::clean($dir);

            // Add trailing separators as needed
            if (substr($dir, -1) !== DIRECTORY_SEPARATOR) {
                // Directory
                $dir .= DIRECTORY_SEPARATOR;
            }

            // Add to the top of the search dirs
            array_unshift($this->_path[$type], $dir);
        }
    }

    /**
     * Create the filename for a resource
     *
     * @param   string  $type   The resource type to create the filename for
     * @param   array   $parts  An associative array of filename information
     *
     * @return  string  The filename
     *
     * @since   3.0
     */
    protected function _createFileName($type, $parts = [])
    {
        switch ($type) {
            case 'template':
                $filename = strtolower($parts['name']) . '.' . $this->_layoutExt;
                break;

            default:
                $filename = strtolower($parts['name']) . '.php';
                break;
        }

        return $filename;
    }

    /**
     * Returns the form object
     *
     * @return  mixed  A \JForm object on success, false on failure
     *
     * @since   3.2
     */
    public function getForm()
    {
        if (!\is_object($this->form)) {
            $this->form = $this->get('Form');
        }

        return $this->form;
    }

    /**
     * Sets the document title according to Global Configuration options
     *
     * @param   string  $title  The page title
     *
     * @return  void
     *
     * @since   3.6
     */
    public function setDocumentTitle($title)
    {
        $app = Factory::getApplication();

        // Check for empty title and add site name if param is set
        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);
    }
}
