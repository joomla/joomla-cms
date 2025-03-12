<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Behaviour.compat
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Behaviour\Compat\Extension;

use Joomla\CMS\Event\Application\AfterInitialiseDocumentEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Compat Plugin.
 *
 * @since  4.4.0
 */
final class Compat extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of CMS events this plugin will listen to and the respective handlers.
     *
     * @return  array
     *
     * @since  4.4.0
     */
    public static function getSubscribedEvents(): array
    {
        /**
         * Note that onAfterInitialise must be the first handlers to run for this
         * plugin to operate as expected. These handlers load compatibility code which
         * might be needed by other plugins
         */
        return [
            'onAfterInitialiseDocument' => ['onAfterInitialiseDocument', Priority::HIGH],
        ];
    }

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $dispatcher  The event dispatcher
     * @param   array                $config      An optional associative array of configuration settings.
     *                                            Recognized key values include 'name', 'group', 'params', 'language'
     *                                            (this list is not meant to be comprehensive).
     *
     * @since   1.5
     */
    public function __construct(DispatcherInterface $dispatcher, array $config = [])
    {
        parent::__construct($dispatcher, $config);

        /**
         * Normally we should never use the constructor to execute any logic which would
         * affect other parts of the cms, but since we need to load class aliases as
         * early as possible we load the class aliases in the constructor so behaviour and system plugins
         * which depend on the JPlugin alias for example still are working
         */

        /**
         * Load class names which are deprecated in joomla 4.0 and which will
         * likely be removed in Joomla 6.0
         */
        if ($this->params->get('classes_aliases', '1')) {
            require_once \dirname(__DIR__) . '/classmap/classmap.php';
        }

        /**
         * Include classes which are removed in 7.0
         */
        if ($this->params->get('legacy_classes', '1')) {
            \JLoader::registerNamespace('\\Joomla\\CMS\\Filesystem', JPATH_PLUGINS . '/behaviour/compat/classes/Filesystem');
        }

        /**
         * Load the constant early as it is used in class files before the class itself is loaded.
         * @deprecated 4.4.0 will be removed in 7.0
         */
        \defined('JPATH_PLATFORM') or \define('JPATH_PLATFORM', __DIR__);
    }

    /**
     * We run as early as possible, this should be the first event
     *
     * @param  AfterInitialiseDocumentEvent $event
     * @return void
     *
     * @since  5.0.0
     */
    public function onAfterInitialiseDocument(AfterInitialiseDocumentEvent $event)
    {
        /**
         * Load the es5 assets stubs, they are needed if an extension
         * directly uses a core es5 asset which has no function in Joomla 5+
         * and only provides an empty asset to not throw an exception
         */
        if ($this->params->get('es5_assets', '1')) {
            $event->getDocument()
                ->getWebAssetManager()
                ->getRegistry()
                ->addRegistryFile('media/plg_behaviour_compat/es5.asset.json');
        }
        /**
         * Load the removed assets stubs, they are needed if an extension
         * directly uses a core asset from Joomla 4 which is not present in Joomla 5+
         * and only provides an empty asset to not throw an exception
         */
        if ($this->params->get('removed_asset', '1')) {
            $event->getDocument()
                ->getWebAssetManager()
                ->getRegistry()
                ->addRegistryFile('media/plg_behaviour_compat/removed.asset.json');
        }
    }
}
