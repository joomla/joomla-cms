<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.sessiongc
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\SessionGC\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\MetadataManager;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Garbage collection handler for session related data
 *
 * @since  3.8.6
 */
final class SessionGC extends CMSPlugin
{
    /**
     * The meta data manager
     *
     * @var   MetadataManager
     *
     * @since 4.4.0
     */
    private $metadataManager;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher       The dispatcher
     * @param   array                $config           An optional associative array of configuration settings
     * @param   MetadataManager      $metadataManager  The user factory
     *
     * @since   4.4.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, MetadataManager $metadataManager)
    {
        parent::__construct($dispatcher, $config);

        $this->metadataManager = $metadataManager;
    }

    /**
     * Runs after the HTTP response has been sent to the client and performs garbage collection tasks
     *
     * @return  void
     *
     * @since   3.8.6
     */
    public function onAfterRespond()
    {
        if ($this->params->get('enable_session_gc', 1)) {
            $probability = $this->params->get('gc_probability', 1);
            $divisor     = $this->params->get('gc_divisor', 100);

            $random = $divisor * lcg_value();

            if ($probability > 0 && $random < $probability) {
                $this->getApplication()->getSession()->gc();
            }
        }

        if ($this->getApplication()->get('session_handler', 'none') !== 'database' && $this->params->get('enable_session_metadata_gc', 1)) {
            $probability = $this->params->get('gc_probability', 1);
            $divisor     = $this->params->get('gc_divisor', 100);

            $random = $divisor * lcg_value();

            if ($probability > 0 && $random < $probability) {
                $this->metadataManager->deletePriorTo(time() - $this->getApplication()->getSession()->getExpire());
            }
        }
    }
}
