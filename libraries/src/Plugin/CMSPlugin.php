<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin;

use Joomla\CMS\Application\ApplicationAwareInterface;
use Joomla\CMS\Application\ApplicationAwareTrait;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin Class
 *
 * @since  1.5
 */
abstract class CMSPlugin implements ApplicationAwareInterface, DispatcherAwareInterface, PluginInterface
{
    use DispatcherAwareTrait;
    use ApplicationAwareTrait;
    use LanguageAwareTrait;
    use LegacyPropertiesTrait;
    use LegacyListenerTrait;

    /**
     * A Registry object holding the parameters for the plugin
     *
     * @var    Registry
     * @since  1.5
     */
    public $params = null;

    /**
     * The name of the plugin
     *
     * @var    string
     * @since  1.5
     */
    //phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected $_name = null;
    //phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

    /**
     * The plugin type
     *
     * @var    string
     * @since  1.5
     */
    //phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected $_type = null;
    //phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1
     * @deprecated 5.0 Use the LanguageAwareTrait and call loadLanguage() manually
     */
    protected $autoloadLanguage = false;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  &$subject  The object to observe
     * @param   array                 $config   An optional associative array of configuration
     *                                          settings. Recognized key values include 'name',
     *                                          'group', 'params', 'language'
     *                                          (this list is not meant to be comprehensive).
     *
     * @since   1.5
     */
    public function __construct(&$subject, $config = [])
    {
        // Get the parameters.
        if (isset($config['params'])) {
            if ($config['params'] instanceof Registry) {
                $this->params = $config['params'];
            } else {
                $this->params = new Registry($config['params']);
            }
        }

        // Get the plugin name and type
        $this->_name = $config['name'] ?? null;
        $this->_type = $config['type'] ?? null;

        // Load the language files if needed.
        if ($this->autoloadLanguage) {
            $this->loadLanguage();
        }

        // Look for and populate the legacy $app and $db properties
        if (method_exists($this, 'implementLegacyProperties')) {
            try {
                $this->implementLegacyProperties();
            } catch (\ReflectionException $e) {
                // Do nothing; the legacy properties will be null.
            }
        }

        // Set the dispatcher we are to register our listeners with
        $this->setDispatcher($subject);
    }
}
