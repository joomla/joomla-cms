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
use Joomla\Event\Event;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;
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
     * @deprecated 5.0 Use the LanguageAwareTrait and call loadLanguage() in doInitialise().
     */
    protected $autoloadLanguage = false;

    /**
     * Should we allow "magic" late initialisation of this plugin using the doInitialise code?
     *
     * @var   bool
     * @since  __DEPLOY_VERSION__
     */
    protected $allowLateInitialisation = true;

    /**
     * Flag for the initialisePlugin method.
     *
     * @var    bool
     * @since  __DEPLOY_VERSION__
     */
    private $isPluginInitialised = false;

    /**
     * Constructor.
     *
     * Do not put any slow initialisation code in it, e.g. code which accesses the database,
     * performs lengthy calculations, or calls external services over HTTP. Put this code into the
     * doInitialise() method which is called ONCE, before the first event handler in your plugin is
     * executed.
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
        $this->autoloadLanguage = $this->autoloadLanguage && method_exists($this, 'loadLanguage');

        if (!$this->allowLateInitialisation && $this->autoloadLanguage) {
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

        // Mark the initialisation code as not yet executed.
        if ($this->allowLateInitialisation) {
            $this->isPluginInitialised = false;
            $this->registerLateInitialisation();
        }
    }

    /**
     * Initialises the plugin before each event is handled.
     *
     * Override the doInitialise() method in your class with your initialisation code.
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    final public function initialisePlugin(Event $e): void
    {
        if ($this->isPluginInitialised) {
            return;
        }

        $this->isPluginInitialised = true;

        $this->doInitialise();
    }

    /**
     * Initialisation code for your plugin.
     *
     * This method is "magically" called exactly once, right before the very first time an event
     * handler in your plugin is called. This makes sure that all lengthy initialisation code in
     * your plugin will only be executed if your plugin is used in a page load, drastically
     * improving the site's performance on page's where your plugin is loaded but its event handlers
     * are not used.
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    protected function doInitialise()
    {
        // Load the language files if needed.
        if ($this->autoloadLanguage) {
            $this->loadLanguage();
        }
    }

    /**
     * Register the "magic" late initialisation code for this plugin
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    private function registerLateInitialisation(): void
    {
        $prioritisedEvents = [];

        // Collect events and their priorities plus one for plugins implementing SubscriberInterface
        if ($this instanceof SubscriberInterface) {
            foreach ($this->getSubscribedEvents() as $eventName => $eventHandlerInfo) {
                $priority = is_array($eventHandlerInfo) ? $eventHandlerInfo[1] : Priority::NORMAL;
                $priority = $priority < PHP_INT_MAX ? $priority++ : $priority;
                $prioritisedEvents[$eventName] = $priority;
            }
        }

        // Collect events and their priorities plus one for plugins using LegacyListenerTrait
        if (property_exists($this, 'legacyListenersDiscovered') && is_array($this->legacyListenersDiscovered)) {
            foreach ($this->legacyListenersDiscovered as $eventName) {
                $prioritisedEvents[$eventName] =
                    max($prioritisedEvents[$eventName] ?? Priority::NORMAL, Priority::ABOVE_NORMAL);
            }
        }

        // Early return if the plugin does not listen to any events.
        if (empty($prioritisedEvents)) {
            return;
        }

        // Make the initialisePlugin code run before each of the plugin's real event handlers.
        array_walk(
            $prioritisedEvents,
            function (int $priority, string $eventName) {
                $this->getDispatcher()->addListener($eventName, [$this, 'initialisePlugin'], $priority);
            }
        );
    }
}
