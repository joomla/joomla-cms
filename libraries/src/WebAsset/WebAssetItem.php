<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Asset Item class
 *
 * Asset Item are "read only" object, all properties must be set through class constructor.
 * Only properties allowed to be edited is an attributes and an options.
 * Changing an uri or a dependencies are not allowed, prefer to create a new asset instance.
 *
 * @since  4.0.0
 */
class WebAssetItem implements WebAssetItemInterface, WebAssetItemCrossDependenciesInterface
{
    /**
     * Asset name
     *
     * @var    string  $name
     * @since  4.0.0
     */
    protected $name = '';

    /**
     * The URI for the asset
     *
     * @var    string
     * @since  4.0.0
     */
    protected $uri = '';

    /**
     * Additional options for the asset
     *
     * @var    array
     * @since  4.0.0
     */
    protected $options = [];

    /**
     * Attributes for the asset, to be rendered in the asset's HTML tag
     *
     * @var    array
     * @since  4.0.0
     */
    protected $attributes = [];

    /**
     * Asset dependencies
     *
     * @var    string[]
     * @since  4.0.0
     */
    protected $dependencies = [];

    /**
     * Unparsed cross dependencies
     *
     * @var    array[]
     * @since  __DEPLOY_VERSION__
     */
    private $rawCrossDependencies = [];

    /**
     * Asset cross dependencies
     *
     * @var    array[]
     * @since  __DEPLOY_VERSION__
     */
    protected $crossDependencies = [];

    /**
     * Asset version
     *
     * @var    string
     * @since  4.0.0
     */
    protected $version = 'auto';

    /**
     * Class constructor
     *
     * @param   string   $name               The asset name
     * @param   ?string  $uri                The URI for the asset
     * @param   array    $options            Additional options for the asset
     * @param   array    $attributes         Attributes for the asset
     * @param   array    $dependencies       Asset dependencies, from assets of the same type
     * @param   array    $crossDependencies  Asset dependencies, from assets of another type
     *
     * @since   4.0.0
     */
    public function __construct(
        string $name,
        ?string $uri = null,
        array $options = [],
        array $attributes = [],
        array $dependencies = [],
        array $crossDependencies = []
    ) {
        $this->name    = $name;
        $this->uri     = $uri;

        if (\array_key_exists('version', $options)) {
            $this->version = $options['version'];
            unset($options['version']);
        }

        if (\array_key_exists('attributes', $options)) {
            $this->attributes = (array) $options['attributes'];
            unset($options['attributes']);
        } else {
            $this->attributes = $attributes;
        }

        if (\array_key_exists('dependencies', $options)) {
            $this->dependencies = (array) $options['dependencies'];
            unset($options['dependencies']);
        } else {
            $this->dependencies = $dependencies;
        }

        if (\array_key_exists('crossDependencies', $options)) {
            $this->rawCrossDependencies = (array) $options['crossDependencies'];
            unset($options['crossDependencies']);
        } else {
            $this->rawCrossDependencies = $crossDependencies;
        }

        $this->options = $options;
    }

    /**
     * Return Asset name
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return Asset version
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getVersion(): string
    {
        return (string) $this->version;
    }

    /**
     * Return dependencies list
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Return associative list of cross dependencies.
     * Example: ['script' => ['script1', 'script2'], 'style' => ['style1', 'style2']]
     *
     * @return  array[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getCrossDependencies(): array
    {
        if ($this->rawCrossDependencies && !$this->crossDependencies) {
            // Cross Dependencies as an associative array
            if (!\is_int(key($this->rawCrossDependencies))) {
                $this->crossDependencies = $this->rawCrossDependencies;
            } else {
                // Parse Cross Dependencies which comes in ["name#type"] format
                foreach ($this->rawCrossDependencies as $crossDependency) {
                    $pos     = strrpos($crossDependency, '#');
                    $depType = $pos ? substr($crossDependency, $pos + 1) : '';
                    $depName = $pos ? substr($crossDependency, 0, $pos) : '';

                    if (!$depType || !$depName) {
                        throw new \UnexpectedValueException(
                            sprintf('Incomplete definition for cross dependency, for asset "%s"', $this->getName())
                        );
                    }

                    if (empty($this->crossDependencies[$depType])) {
                        $this->crossDependencies[$depType] = [];
                    }

                    $this->crossDependencies[$depType][] = $depName;
                }
            }
            $this->rawCrossDependencies = [];
        }

        return $this->crossDependencies;
    }

    /**
     * Get the file path
     *
     * @param   boolean  $resolvePath  Whether need to search for a real paths
     *
     * @return  string  The resolved path if resolved, else an empty string.
     *
     * @since   4.0.0
     */
    public function getUri($resolvePath = true): string
    {
        $path = $this->uri;

        if ($resolvePath && $path) {
            switch (pathinfo($path, PATHINFO_EXTENSION)) {
                case 'js':
                    $path = $this->resolvePath($path, 'script');
                    break;
                case 'css':
                    $path = $this->resolvePath($path, 'stylesheet');
                    break;
                default:
                    // Asset for the ES modules may give us a folder for ESM import map
                    if (str_ends_with($path, '/') && !str_starts_with($path, '.')) {
                        $path = Uri::root(true) . '/' . $path;
                    }
                    break;
            }
        }

        return $path ?? '';
    }

    /**
     * Get the option
     *
     * @param   string  $key      An option key
     * @param   string  $default  A default value
     *
     * @return mixed
     *
     * @since   4.0.0
     */
    public function getOption(string $key, $default = null)
    {
        if (\array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        return $default;
    }

    /**
     * Set the option
     *
     * @param   string  $key    An option key
     * @param   string  $value  An option value
     *
     * @return self
     *
     * @since   4.0.0
     */
    public function setOption(string $key, $value = null): WebAssetItemInterface
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Get all options
     *
     * @return array
     *
     * @since   4.0.0
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the attribute
     *
     * @param   string  $key      An attributes key
     * @param   string  $default  A default value
     *
     * @return mixed
     *
     * @since   4.0.0
     */
    public function getAttribute(string $key, $default = null)
    {
        if (\array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    /**
     * Set the attribute
     *
     * @param   string  $key    An attribute key
     * @param   string  $value  An attribute value
     *
     * @return self
     *
     * @since   4.0.0
     */
    public function setAttribute(string $key, $value = null): WebAssetItemInterface
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get all attributes
     *
     * @return array
     *
     * @since   4.0.0
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Resolve path
     *
     * @param   string  $path  The path to resolve
     * @param   string  $type  The resolver method
     *
     * @return string
     *
     * @since  4.0.0
     */
    protected function resolvePath(string $path, string $type): string
    {
        if ($type !== 'script' && $type !== 'stylesheet') {
            throw new \UnexpectedValueException('Unexpected [type], expected "script" or "stylesheet"');
        }

        $file     = $path;
        $external = $this->isPathExternal($path);

        if (!$external) {
            // Get the file path
            $file = HTMLHelper::_(
                $type,
                $path,
                [
                    'pathOnly' => true,
                    'relative' => !$this->isPathAbsolute($path),
                ]
            );
        }

        return $file ?? '';
    }

    /**
     * Check if the Path is External
     *
     * @param   string  $path  Path to test
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function isPathExternal(string $path): bool
    {
        return strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0 || strpos($path, '//') === 0;
    }

    /**
     * Check if the Path is relative to /media folder or absolute
     *
     * @param   string  $path  Path to test
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function isPathAbsolute(string $path): bool
    {
        // We have a full path or not
        return strpos($path, '/') !== false && is_file(JPATH_ROOT . '/' . $path);
    }
}
