<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') || die;

use Joomla\Application\ApplicationInterface;
use Joomla\Application\SessionAwareWebApplicationInterface;
use Joomla\CMS\Event\LazyServiceSubscriber;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Webauthn\Authentication;
use Joomla\Plugin\System\Webauthn\CredentialRepository;
use Joomla\Plugin\System\Webauthn\Extension\Webauthn;
use Joomla\Plugin\System\Webauthn\MetadataRepository;
use Joomla\Registry\Registry;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\PublicKeyCredentialSourceRepository;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function register(Container $container)
    {
        $container->set(
            Webauthn::class,
            function (Container $container) {
                $app     = Factory::getApplication();
                $session = $container->has('session') ? $container->get('session') : $this->getSession($app);

                $db                    = $container->get(DatabaseInterface::class);
                $credentialsRepository = $container->has(PublicKeyCredentialSourceRepository::class)
                    ? $container->get(PublicKeyCredentialSourceRepository::class)
                    : new CredentialRepository($db);

                $metadataRepository = null;
                $params             = new Registry($config['params'] ?? '{}');

                if ($params->get('attestationSupport', 0) == 1) {
                    $metadataRepository    = $container->has(MetadataStatementRepository::class)
                        ? $container->get(MetadataStatementRepository::class)
                        : new MetadataRepository();
                }

                $authenticationHelper  = $container->has(Authentication::class)
                    ? $container->get(Authentication::class)
                    : new Authentication($app, $session, $credentialsRepository, $metadataRepository);

                $plugin = new Webauthn(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'webauthn'),
                    $authenticationHelper
                );
                $plugin->setApplication($app);

                return $plugin;
            }
        )->set(
            PluginInterface::class,
            function (Container $container) {
                return new LazyServiceSubscriber($container, Webauthn::class);
            }
        );
    }

    /**
     * Get the current application session object
     *
     * @param   ApplicationInterface  $app  The application we are running in
     *
     * @return \Joomla\Session\SessionInterface|null
     *
     * @since  4.2.0
     */
    private function getSession(ApplicationInterface $app)
    {
        return $app instanceof SessionAwareWebApplicationInterface ? $app->getSession() : null;
    }
};
