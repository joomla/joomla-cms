<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class JNamespacePsr4Map
 *
 * @since  4.0.0
 */
class JNamespacePsr4Map
{
    /**
     * Path to the autoloader
     *
     * @var    string
     * @since  4.0.0
     */
    protected $file = JPATH_CACHE . '/autoload_psr4.php';

    /**
     * @var array|null
     * @since 4.0.0
     */
    private $cachedMap = null;

    /**
     * Check if the file exists
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function exists()
    {
        return is_file($this->file);
    }

    /**
     * Check if the namespace mapping file exists, if not create it
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function ensureMapFileExists()
    {
        if (!$this->exists()) {
            $this->create();
        }
    }

    /**
     * Create the namespace file
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function create()
    {
        $extensions = array_merge(
            $this->getNamespaces('component'),
            $this->getNamespaces('module'),
            $this->getNamespaces('template'),
            $this->getNamespaces('plugin'),
            $this->getNamespaces('library')
        );

        ksort($extensions);

        $this->writeNamespaceFile($extensions);

        return true;
    }

    /**
     * Load the PSR4 file
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function load()
    {
        if (!$this->exists()) {
            $this->create();
        }

        $map = $this->cachedMap ?: require $this->file;

        $loader = include JPATH_LIBRARIES . '/vendor/autoload.php';

        foreach ($map as $namespace => $path) {
            $loader->setPsr4($namespace, $path);
        }

        return true;
    }

    /**
     * Write the Namespace mapping file
     *
     * @param   array  $elements  Array of elements
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function writeNamespaceFile($elements)
    {
        $content   = [];
        $content[] = "<?php";
        $content[] = 'defined(\'_JEXEC\') or die;';
        $content[] = 'return [';

        foreach ($elements as $namespace => $path) {
            $content[] = "\t'" . $namespace . "'" . ' => [' . $path . '],';
        }

        $content[] = '];';

        /**
         * Backup the current error_reporting level and set a new level
         *
         * We do this because file_put_contents can raise a Warning if it cannot write the autoload_psr4.php file
         * and this will output to the response BEFORE the session has started, causing the session start to fail
         * and ultimately leading us to a 500 Internal Server Error page just because of the output warning, which
         * we can safely ignore as we can use an in-memory autoload_psr4 map temporarily, and display real errors later.
         */
        $error_reporting = error_reporting(0);

        try {
            File::write($this->file, implode("\n", $content));
        } catch (Exception $e) {
            Log::add('Could not save ' . $this->file, Log::WARNING);

            $map = [];
            $constants = ['JPATH_ADMINISTRATOR', 'JPATH_API', 'JPATH_SITE', 'JPATH_PLUGINS', 'JPATH_LIBRARIES'];

            foreach ($elements as $namespace => $path) {
                foreach ($constants as $constant) {
                    $path = preg_replace(['/^(' . $constant . ")\s\.\s\'/", '/\'$/'], [constant($constant), ''], $path);
                }

                $namespace = str_replace('\\\\', '\\', $namespace);
                $map[$namespace] = [ $path ];
            }

            $this->cachedMap = $map;
        }

        // Restore previous value of error_reporting
        error_reporting($error_reporting);
    }

    /**
     * Get an array of namespaces with their respective path for the given extension type.
     *
     * @param   string  $type  The extension type
     *
     * @return  array
     *
     * @since   4.0.0
     */
    private function getNamespaces(string $type): array
    {
        $extensions = [];

        foreach ($this->getExtensions($type) as $extensionPath => $file) {
            // Load the manifest file
            $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOERROR);

            // When invalid, ignore
            if (!$xml) {
                continue;
            }

            // The namespace node
            $namespaceNode = $xml->namespace;

            // The namespace string
            $namespace = (string) $namespaceNode;

            // Ignore when the string is empty
            if (!$namespace) {
                continue;
            }

            // Normalize the namespace string
            $namespace     = str_replace('\\', '\\\\', $namespace) . '\\\\';
            $namespacePath = rtrim($extensionPath . '/' . $namespaceNode->attributes()->path, '/');

            if ($type === 'plugin' || $type === 'library') {
                $baseDir = $type === 'plugin' ? 'JPATH_PLUGINS . \'' : 'JPATH_LIBRARIES . \'';
                $path    = substr($namespacePath, strlen($type === 'plugin' ? JPATH_PLUGINS : JPATH_LIBRARIES));

                // Set the namespace
                $extensions[$namespace] = $baseDir . $path . '\'';

                continue;
            }

            // Check if we need to use administrator path
            $isAdministrator = strpos($namespacePath, JPATH_ADMINISTRATOR) === 0;
            $path            = substr($namespacePath, strlen($isAdministrator ? JPATH_ADMINISTRATOR : JPATH_SITE));

            // Add the site path when a component
            if ($type === 'component') {
                if (is_dir(JPATH_SITE . $path)) {
                    $extensions[$namespace . 'Site\\\\'] = 'JPATH_SITE . \'' . $path . '\'';
                }

                if (is_dir(JPATH_API . $path)) {
                    $extensions[$namespace . 'Api\\\\'] = 'JPATH_API . \'' . $path . '\'';
                }
            }

            // Add the application specific segment when a component or module
            $baseDir    = $isAdministrator ? 'JPATH_ADMINISTRATOR . \'' : 'JPATH_SITE . \'';
            $realPath   = ($isAdministrator ? JPATH_ADMINISTRATOR : JPATH_SITE) . $path;
            $namespace .= $isAdministrator ? 'Administrator\\\\' : 'Site\\\\';

            // Validate if the directory exists
            if (!is_dir($realPath)) {
                continue;
            }

            // Set the namespace
            $extensions[$namespace] = $baseDir . $path . '\'';
        }

        // Return the namespaces
        return $extensions;
    }

    /**
     * Returns an array of extensions with their respective paths as keys and manifest paths as values.
     *
     * @param   string  $type  The extension type
     *
     * @return  array
     *
     * @since   4.3.0
     */
    private function getExtensions(string $type): array
    {
        $manifests = [];

        if ($type === 'library') {
            try {
                // Scan library manifest directories for XML files
                foreach (Folder::files(JPATH_MANIFESTS . '/libraries', '\.xml$', true, true) as $file) {
                    // Match manifest to extension directory
                    $manifests[JPATH_LIBRARIES . '/' . File::stripExt(substr($file, strlen(JPATH_MANIFESTS . '/libraries') + 1))] = $file;
                }
            } catch (UnexpectedValueException $e) {
                return [];
            }

            return $manifests;
        }

        if ($type === 'component') {
            $directories = [JPATH_ADMINISTRATOR . '/components'];
        } elseif ($type === 'module') {
            $directories = [JPATH_SITE . '/modules', JPATH_ADMINISTRATOR . '/modules'];
        } elseif ($type === 'template') {
            $directories = [JPATH_SITE . '/templates', JPATH_ADMINISTRATOR . '/templates'];
        } else {
            try {
                $directories = Folder::folders(JPATH_PLUGINS, '.', false, true);
            } catch (UnexpectedValueException $e) {
                $directories = [];
            }
        }

        foreach ($directories as $directory) {
            try {
                $extensionDirectories = Folder::folders($directory, '.', false, false);
            } catch (UnexpectedValueException $e) {
                continue;
            }

            foreach ($extensionDirectories as $extension) {
                // Compile the extension path
                $extensionPath = $directory . '/' . $extension;

                if ($type === 'component') {
                    // Strip the com_ from the extension name for components
                    $file = $extensionPath . '/' . substr($extension, 4) . '.xml';

                    if (!is_file($file)) {
                        $file = $extensionPath . '/' . $extension . '.xml';
                    }
                } elseif ($type === 'template') {
                    // Template manifestfiles have a fix filename
                    $file = $extensionPath . '/templateDetails.xml';
                } else {
                    $file = $extensionPath . '/' . $extension . '.xml';
                }

                if (is_file($file)) {
                    $manifests[$extensionPath] = $file;
                }
            }
        }

        return $manifests;
    }
}
