<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\CacheControllerFactoryAwareInterface;
use Joomla\CMS\Cache\CacheControllerFactoryAwareTrait;
use Joomla\CMS\Factory as CmsFactory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Utility\Utility;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HtmlDocument class, provides an easy interface to parse and display a HTML document
 *
 * @since  1.7.0
 */
class HtmlDocument extends Document implements CacheControllerFactoryAwareInterface
{
    use CacheControllerFactoryAwareTrait;

    /**
     * Array of Header `<link>` tags
     *
     * @var    array
     * @since  1.7.0
     */
    public $_links = [];

    /**
     * Array of custom tags
     *
     * @var    array
     * @since  1.7.0
     */
    public $_custom = [];

    /**
     * Name of the template
     *
     * @var    string
     * @since  1.7.0
     */
    public $template = null;

    /**
     * Base url
     *
     * @var    string
     * @since  1.7.0
     */
    public $baseurl = null;

    /**
     * Array of template parameters
     *
     * @var    array
     * @since  1.7.0
     */
    public $params = null;

    /**
     * File name
     *
     * @var    array
     * @since  1.7.0
     */
    public $_file = null;

    /**
     * Script nonce (string if set, null otherwise)
     *
     * @var    string|null
     * @since  4.0.0
     */
    public $cspNonce = null;

    /**
     * String holding parsed template
     *
     * @var    string
     * @since  1.7.0
     */
    protected $_template = '';

    /**
     * Array of parsed template JDoc tags
     *
     * @var    array
     * @since  1.7.0
     */
    protected $_template_tags = [];

    /**
     * Integer with caching setting
     *
     * @var    integer
     * @since  1.7.0
     */
    protected $_caching = null;

    /**
     * Set to true when the document should be output as HTML5
     *
     * @var    boolean
     * @since  4.0.0
     */
    private $html5 = true;

    /**
     * Class constructor
     *
     * @param   array  $options  Associative array of options
     *
     * @since   1.7.0
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        // Set document type
        $this->_type = 'html';

        // Set default mime type and document metadata (metadata syncs with mime type by default)
        $this->setMimeEncoding('text/html');
    }

    /**
     * Get the HTML document head data
     *
     * @return  array  The document head data in array form
     *
     * @since   1.7.0
     */
    public function getHeadData()
    {
        $data                  = [];
        $data['title']         = $this->title;
        $data['description']   = $this->description;
        $data['link']          = $this->link;
        $data['metaTags']      = $this->_metaTags;
        $data['links']         = $this->_links;
        $data['styleSheets']   = $this->_styleSheets;
        $data['style']         = $this->_style;
        $data['scripts']       = $this->_scripts;
        $data['script']        = $this->_script;
        $data['custom']        = $this->_custom;

        /**
         * @deprecated  4.0 will be removed in 6.0
         *              This property is for backwards compatibility. Pass text through script options in the future
         */
        $data['scriptText']    = Text::getScriptStrings();

        $data['scriptOptions'] = $this->scriptOptions;

        // Get Asset manager state
        $wa      = $this->getWebAssetManager();
        $waState = $wa->getManagerState();

        // Get asset objects and filter only manually added/enabled assets,
        // Dependencies will be picked up from registry files
        $waState['assets'] = [];

        foreach ($waState['activeAssets'] as $assetType => $assetNames) {
            foreach ($assetNames as $assetName => $assetState) {
                $waState['assets'][$assetType][] = $wa->getAsset($assetType, $assetName);
            }
        }

        // We have loaded asset objects, now can remove unused stuff
        unset($waState['activeAssets']);

        $data['assetManager'] = $waState;

        return $data;
    }

    /**
     * Reset the HTML document head data
     *
     * @param   mixed  $types  type or types of the heads elements to reset
     *
     * @return  HtmlDocument  instance of $this to allow chaining
     *
     * @since   3.7.0
     */
    public function resetHeadData($types = null)
    {
        if (\is_null($types)) {
            $this->title         = '';
            $this->description   = '';
            $this->link          = '';
            $this->_metaTags     = [];
            $this->_links        = [];
            $this->_styleSheets  = [];
            $this->_style        = [];
            $this->_scripts      = [];
            $this->_script       = [];
            $this->_custom       = [];
            $this->scriptOptions = [];
        }

        if (\is_array($types)) {
            foreach ($types as $type) {
                $this->resetHeadDatum($type);
            }
        }

        if (\is_string($types)) {
            $this->resetHeadDatum($types);
        }

        return $this;
    }

    /**
     * Reset a part the HTML document head data
     *
     * @param   string  $type  type of the heads elements to reset
     *
     * @return  void
     *
     * @since   3.7.0
     */
    private function resetHeadDatum($type)
    {
        switch ($type) {
            case 'title':
            case 'description':
            case 'link':
                $this->{$type} = '';
                break;

            case 'metaTags':
            case 'links':
            case 'styleSheets':
            case 'style':
            case 'scripts':
            case 'script':
            case 'custom':
                $realType          = '_' . $type;
                $this->{$realType} = [];
                break;

            case 'scriptOptions':
                $this->{$type} = [];
                break;
        }
    }

    /**
     * Set the HTML document head data
     *
     * @param   array  $data  The document head data in array form
     *
     * @return  HtmlDocument|null instance of $this to allow chaining or null for empty input data
     *
     * @since   1.7.0
     */
    public function setHeadData($data)
    {
        if (empty($data) || !\is_array($data)) {
            return null;
        }

        $this->title         = $data['title'] ?? $this->title;
        $this->description   = $data['description'] ?? $this->description;
        $this->link          = $data['link'] ?? $this->link;
        $this->_metaTags     = $data['metaTags'] ?? $this->_metaTags;
        $this->_links        = $data['links'] ?? $this->_links;
        $this->_styleSheets  = $data['styleSheets'] ?? $this->_styleSheets;
        $this->_style        = $data['style'] ?? $this->_style;
        $this->_scripts      = $data['scripts'] ?? $this->_scripts;
        $this->_script       = $data['script'] ?? $this->_script;
        $this->_custom       = $data['custom'] ?? $this->_custom;
        $this->scriptOptions = (isset($data['scriptOptions']) && !empty($data['scriptOptions'])) ? $data['scriptOptions'] : $this->scriptOptions;

        // Restore asset manager state
        $wa = $this->getWebAssetManager();

        if (!empty($data['assetManager']['registryFiles'])) {
            $waRegistry = $wa->getRegistry();

            foreach ($data['assetManager']['registryFiles'] as $registryFile) {
                $waRegistry->addRegistryFile($registryFile);
            }
        }

        if (!empty($data['assetManager']['assets'])) {
            foreach ($data['assetManager']['assets'] as $assetType => $assets) {
                foreach ($assets as $asset) {
                    $wa->registerAsset($assetType, $asset)->useAsset($assetType, $asset->getName());
                }
            }
        }

        return $this;
    }

    /**
     * Merge the HTML document head data
     *
     * @param   array  $data  The document head data in array form
     *
     * @return  HtmlDocument  instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function mergeHeadData($data)
    {
        if (empty($data) || !\is_array($data)) {
            return $this;
        }

        $this->title = (isset($data['title']) && !empty($data['title']) && !stristr($this->title, $data['title']))
            ? $this->title . $data['title']
            : $this->title;
        $this->description = (isset($data['description']) && !empty($data['description']) && !stristr($this->description, $data['description']))
            ? $this->description . $data['description']
            : $this->description;
        $this->link = $data['link'] ?? $this->link;

        if (isset($data['metaTags'])) {
            foreach ($data['metaTags'] as $type1 => $data1) {
                $booldog = $type1 === 'http-equiv';

                foreach ($data1 as $name2 => $data2) {
                    $this->setMetaData($name2, $data2, $booldog);
                }
            }
        }

        $this->_links = (isset($data['links']) && !empty($data['links']) && \is_array($data['links']))
            ? array_unique(array_merge($this->_links, $data['links']), SORT_REGULAR)
            : $this->_links;
        $this->_styleSheets = (isset($data['styleSheets']) && !empty($data['styleSheets']) && \is_array($data['styleSheets']))
            ? array_merge($this->_styleSheets, $data['styleSheets'])
            : $this->_styleSheets;

        if (isset($data['style'])) {
            foreach ($data['style'] as $type => $styles) {
                foreach ($styles as $hash => $style) {
                    if (!isset($this->_style[strtolower($type)][$hash])) {
                        $this->addStyleDeclaration($style, $type);
                    }
                }
            }
        }

        $this->_scripts = (isset($data['scripts']) && !empty($data['scripts']) && \is_array($data['scripts']))
            ? array_merge($this->_scripts, $data['scripts'])
            : $this->_scripts;

        if (isset($data['script'])) {
            foreach ($data['script'] as $type => $scripts) {
                foreach ($scripts as $hash => $script) {
                    if (!isset($this->_script[strtolower($type)][$hash])) {
                        $this->addScriptDeclaration($script, $type);
                    }
                }
            }
        }

        $this->_custom = (isset($data['custom']) && !empty($data['custom']) && \is_array($data['custom']))
            ? array_unique(array_merge($this->_custom, $data['custom']))
            : $this->_custom;

        if (!empty($data['scriptOptions'])) {
            foreach ($data['scriptOptions'] as $key => $scriptOptions) {
                $this->addScriptOptions($key, $scriptOptions, true);
            }
        }

        // Restore asset manager state
        $wa = $this->getWebAssetManager();

        if (!empty($data['assetManager']['registryFiles'])) {
            $waRegistry = $wa->getRegistry();

            foreach ($data['assetManager']['registryFiles'] as $registryFile) {
                $waRegistry->addRegistryFile($registryFile);
            }
        }

        if (!empty($data['assetManager']['assets'])) {
            foreach ($data['assetManager']['assets'] as $assetType => $assets) {
                foreach ($assets as $asset) {
                    $wa->registerAsset($assetType, $asset)->useAsset($assetType, $asset->getName());
                }
            }
        }

        return $this;
    }

    /**
     * Adds `<link>` tags to the head of the document
     *
     * $relType defaults to 'rel' as it is the most common relation type used.
     * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
     * Typical tag: `<link href="index.php" rel="Start">`
     *
     * @param   string  $href      The link that is being related.
     * @param   string  $relation  Relation of link.
     * @param   string  $relType   Relation type attribute.  Either rel or rev (default: 'rel').
     * @param   array   $attribs   Associative array of remaining attributes.
     *
     * @return  HtmlDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function addHeadLink($href, $relation, $relType = 'rel', $attribs = [])
    {
        $this->_links[$href]['relation'] = $relation;
        $this->_links[$href]['relType']  = $relType;
        $this->_links[$href]['attribs']  = $attribs;

        return $this;
    }

    /**
     * Adds a shortcut icon (favicon)
     *
     * This adds a link to the icon shown in the favorites list or on
     * the left of the url in the address bar. Some browsers display
     * it on the tab, as well.
     *
     * @param   string  $href      The link that is being related.
     * @param   string  $type      File type
     * @param   string  $relation  Relation of link
     *
     * @return  HtmlDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function addFavicon($href, $type = 'image/vnd.microsoft.icon', $relation = 'shortcut icon')
    {
        $href = str_replace('\\', '/', $href);
        $this->addHeadLink($href, $relation, 'rel', ['type' => $type]);

        return $this;
    }

    /**
     * Adds a custom HTML string to the head block
     *
     * @param   string  $html  The HTML to add to the head
     *
     * @return  HtmlDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function addCustomTag($html)
    {
        $this->_custom[] = trim($html);

        return $this;
    }

    /**
     * Returns whether the document is set up to be output as HTML5
     *
     * @return  boolean true when HTML5 is used
     *
     * @since   3.0.0
     */
    public function isHtml5()
    {
        return $this->html5;
    }

    /**
     * Sets whether the document should be output as HTML5
     *
     * @param   bool  $state  True when HTML5 should be output
     *
     * @return  void
     *
     * @since   3.0.0
     */
    public function setHtml5($state)
    {
        if (\is_bool($state)) {
            $this->html5 = $state;
        }
    }

    /**
     * Get the contents of a document include
     *
     * @param   string  $type     The type of renderer
     * @param   string  $name     The name of the element to render
     * @param   array   $attribs  Associative array of remaining attributes.
     *
     * @return  mixed|string The output of the renderer
     *
     * @since   1.7.0
     */
    public function getBuffer($type = null, $name = null, $attribs = [])
    {
        // If no type is specified, return the whole buffer
        if ($type === null) {
            return parent::$_buffer;
        }

        $title = $attribs['title'] ?? null;

        if (isset(parent::$_buffer[$type][$name][$title])) {
            return parent::$_buffer[$type][$name][$title];
        }

        $renderer = $this->loadRenderer($type);

        if ($this->_caching == true && $type === 'modules' && $name !== 'debug') {
            /** @var  \Joomla\CMS\Document\Renderer\Html\ModulesRenderer  $renderer */
            /** @var  \Joomla\CMS\Cache\Controller\OutputController  $cache */
            $cache  = $this->getCacheControllerFactory()->createCacheController('output', ['defaultgroup' => 'com_modules']);
            $itemId = (int) CmsFactory::getApplication()->getInput()->get('Itemid', 0, 'int');

            $hash = md5(
                serialize(
                    [
                        $name,
                        $attribs,
                        \get_class($renderer),
                        $itemId,
                    ]
                )
            );
            $cbuffer = $cache->get('cbuffer_' . $type) ?: [];

            if (isset($cbuffer[$hash])) {
                return Cache::getWorkarounds($cbuffer[$hash], ['mergehead' => 1]);
            }

            $options               = [];
            $options['nopathway']  = 1;
            $options['nomodules']  = 1;
            $options['modulemode'] = 1;

            $this->setBuffer($renderer->render($name, $attribs, null), $type, $name);
            $data = parent::$_buffer[$type][$name][$title];

            $tmpdata = Cache::setWorkarounds($data, $options);

            $cbuffer[$hash] = $tmpdata;

            $cache->store($cbuffer, 'cbuffer_' . $type);
        } else {
            $this->setBuffer($renderer->render($name, $attribs, null), $type, $name, $title);
        }

        return parent::$_buffer[$type][$name][$title];
    }

    /**
     * Set the contents a document includes
     *
     * @param   string  $content  The content to be set in the buffer.
     * @param   array   $options  Array of optional elements.
     *
     * @return  HtmlDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function setBuffer($content, $options = [])
    {
        // The following code is just for backward compatibility.
        if (\func_num_args() > 1 && !\is_array($options)) {
            $args             = \func_get_args();
            $options          = [];
            $options['type']  = $args[1];
            $options['name']  = $args[2] ?? null;
            $options['title'] = $args[3] ?? null;
        }

        $type  = $options['type'] ?? '';
        $name  = $options['name'] ?? '';
        $title = $options['title'] ?? '';

        parent::$_buffer[$type][$name][$title] = $content;

        return $this;
    }

    /**
     * Parses the template and populates the buffer
     *
     * @param   array  $params  Parameters for fetching the template
     *
     * @return  HtmlDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function parse($params = [])
    {
        return $this->_fetchTemplate($params)->_parseTemplate();
    }

    /**
     * Outputs the template to the browser.
     *
     * @param   boolean  $caching  If true, cache the output
     * @param   array    $params   Associative array of attributes
     *
     * @return  string The rendered data
     *
     * @since   1.7.0
     */
    public function render($caching = false, $params = [])
    {
        $this->_caching = $caching;

        if (empty($this->_template)) {
            $this->parse($params);
        }

        if (\array_key_exists('csp_nonce', $params) && $params['csp_nonce'] !== null) {
            $this->cspNonce = $params['csp_nonce'];
        }

        $data = $this->_renderTemplate();
        parent::render($caching, $params);

        return $data;
    }

    /**
     * Count the modules in the given position
     *
     * @param   string   $positionName     The position to use
     * @param   boolean  $withContentOnly  Count only a modules which actually has a content
     *
     * @return  integer  Number of modules found
     *
     * @since   1.7.0
     */
    public function countModules(string $positionName, bool $withContentOnly = false)
    {
        if ((isset(parent::$_buffer['modules'][$positionName])) && (parent::$_buffer['modules'][$positionName] === false)) {
            return 0;
        }

        $modules = ModuleHelper::getModules($positionName);

        if (!$withContentOnly) {
            return \count($modules);
        }

        // Now we need to count only modules which actually have a content
        $result   = 0;
        $renderer = $this->loadRenderer('module');

        foreach ($modules as $module) {
            if (empty($module->contentRendered)) {
                $renderer->render($module, ['contentOnly' => true]);
            }

            if (trim($module->content) !== '') {
                $result++;
            }
        }

        return $result;
    }

    /**
     * Count the number of child menu items of the current active menu item
     *
     * @return  integer  Number of child menu items
     *
     * @since   1.7.0
     */
    public function countMenuChildren()
    {
        static $children;

        if (!isset($children)) {
            $db       = CmsFactory::getDbo();
            $app      = CmsFactory::getApplication();
            $menu     = $app->getMenu();
            $active   = $menu->getActive();
            $children = 0;

            if ($active) {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__menu'))
                    ->where(
                        [
                            $db->quoteName('parent_id') . ' = :id',
                            $db->quoteName('published') . ' = 1',
                        ]
                    )
                    ->bind(':id', $active->id, ParameterType::INTEGER);
                $db->setQuery($query);
                $children = $db->loadResult();
            }
        }

        return $children;
    }

    /**
     * Load a template file
     *
     * @param   string  $directory  The name of the template
     * @param   string  $filename   The actual filename
     *
     * @return  string  The contents of the template
     *
     * @since   1.7.0
     */
    protected function _loadTemplate($directory, $filename)
    {
        $contents = '';

        // Check to see if we have a valid template file
        if (is_file($directory . '/' . $filename)) {
            // Store the file path
            $this->_file = $directory . '/' . $filename;

            // Get the file content
            ob_start();
            require $directory . '/' . $filename;
            $contents = ob_get_contents();
            ob_end_clean();
        }

        return $contents;
    }

    /**
     * Fetch the template, and initialise the params
     *
     * @param   array  $params  Parameters to determine the template
     *
     * @return  HtmlDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    protected function _fetchTemplate($params = [])
    {
        // Check
        $directory = $params['directory'] ?? 'templates';
        $filter    = InputFilter::getInstance();
        $template  = $filter->clean($params['template'], 'cmd');
        $file      = $filter->clean($params['file'], 'cmd');
        $inherits  = $params['templateInherits'] ?? '';
        $baseDir   = $directory . '/' . $template;

        if (!is_file($directory . '/' . $template . '/' . $file)) {
            if ($inherits !== '' && is_file($directory . '/' . $inherits . '/' . $file)) {
                $baseDir = $directory . '/' . $inherits;
            } else {
                $baseDir  = $directory . '/system';
                $template = 'system';

                if ($file !== 'index.php' && !is_file($baseDir . '/' . $file)) {
                    $file = 'index.php';
                }
            }
        }

        // Load the language file for the template
        $lang = CmsFactory::getLanguage();

        // 1.5 or core then 1.6
        $lang->load('tpl_' . $template, JPATH_BASE)
            || ($inherits !== '' && $lang->load('tpl_' . $inherits, JPATH_BASE))
            || $lang->load('tpl_' . $template, $directory . '/' . $template)
            || ($inherits !== '' && $lang->load('tpl_' . $inherits, $directory . '/' . $inherits));

        // Assign the variables
        $this->baseurl  = Uri::base(true);
        $this->params   = $params['params'] ?? new Registry();
        $this->template = $template;

        // Load
        $this->_template = $this->_loadTemplate($baseDir, $file);

        return $this;
    }

    /**
     * Parse a document template
     *
     * @return  HtmlDocument  instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    protected function _parseTemplate()
    {
        $matches = [];

        if (preg_match_all('#<jdoc:include\ type="([^"]+)"(.*)\/>#iU', $this->_template, $matches)) {
            $messages            = [];
            $template_tags_first = [];
            $template_tags_last  = [];

            // Step through the jdocs in reverse order.
            for ($i = \count($matches[0]) - 1; $i >= 0; $i--) {
                $type    = $matches[1][$i];
                $attribs = empty($matches[2][$i]) ? [] : Utility::parseAttributes($matches[2][$i]);
                $name    = $attribs['name'] ?? null;

                // Separate buffers to be executed first and last
                if ($type === 'module' || $type === 'modules') {
                    $template_tags_first[$matches[0][$i]] = ['type' => $type, 'name' => $name, 'attribs' => $attribs];
                } elseif ($type === 'message') {
                    $messages = [$matches[0][$i] => ['type' => $type, 'name' => $name, 'attribs' => $attribs]];
                } else {
                    $template_tags_last[$matches[0][$i]] = ['type' => $type, 'name' => $name, 'attribs' => $attribs];
                }
            }

            $this->_template_tags = $template_tags_first + $messages + array_reverse($template_tags_last);
        }

        return $this;
    }

    /**
     * Render pre-parsed template
     *
     * @return string rendered template
     *
     * @since   1.7.0
     */
    protected function _renderTemplate()
    {
        $replace = [];
        $with    = [];

        foreach ($this->_template_tags as $jdoc => $args) {
            $replace[] = $jdoc;
            $with[]    = $this->getBuffer($args['type'], $args['name'], $args['attribs']);
        }

        return str_replace($replace, $with, $this->_template);
    }
}
