<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.compat
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Compat\Extension;

use Joomla\CMS\Document\FactoryInterface as DocumentFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Router\SiteRouter;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Page Compat Plugin.
 *
 * @since  5.0.0
 */
final class Compat extends CMSPlugin implements SubscriberInterface
{
    /**
     * The application's document factory interface
     *
     * @var   DocumentFactoryInterface
     * @since 4.2.0
     */
    private $documentFactory;

    /**
     * The application profiler, used when Debug Site is set to Yes in Global Configuration.
     *
     * @var    Profiler|null
     * @since  4.2.0
     */
    private $profiler;

    /**
     * The frontend router, injected by the service provider.
     *
     * @var   SiteRouter|null
     * @since 4.2.0
     */
    private $router;

    /**
     * Constructor
     *
     * @param   DispatcherInterface              $subject                 The object to observe
     * @param   array                            $config                  An optional associative
     *                                                                    array of configuration
     *                                                                    settings. Recognized key
     *                                                                    values include 'name',
     *                                                                    'group', 'params',
     *                                                                    'language'
     *                                                                    (this list is not meant
     *                                                                    to be comprehensive).
     * @param   Profiler|null                    $profiler                The application profiler
     * @param   SiteRouter|null                  $router                  The frontend router
     *
     * @since   4.2.0
     */
    public function __construct(
        &$subject,
        $config,
        ?Profiler $profiler,
        ?SiteRouter $router
    ) {
        parent::__construct($subject, $config);

        $this->profiler               = $profiler;
        $this->router                 = $router;
    }

    /**
     * Returns an array of CMS events this plugin will listen to and the respective handlers.
     *
     * @return  array
     *
     * @since   4.2.0
     */
    public static function getSubscribedEvents(): array
    {
        /**
         * Note that onAfterInitialise must be the first handlers to run for this
         * plugin to operate as expected. These handlers load compatibility code which
         * might be needed by other plugins
         */
        return [
            'onAfterInitialise' => ['onAfterInitialise', Priority::HIGH],
        ];
    }

    public function onAfterInitialise(Event $event)
    {
        /**
         * Load class names which are deprecated in joomla 4.0 and which will
         * likely be removed in Joomla 6.0
         */
        if ($this->params->get('namespaced_classes')) {
            require_once dirname(__DIR__) . '/classmap/classmap.php';
        }
    }
}
