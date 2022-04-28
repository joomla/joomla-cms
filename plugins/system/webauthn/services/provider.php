<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

use Joomla\Application\ApplicationInterface;
use Joomla\Application\SessionAwareWebApplicationInterface;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Webauthn\Authentication;
use Joomla\Plugin\System\Webauthn\CredentialRepository;
use Joomla\Plugin\System\Webauthn\Extension\Webauthn;
use Joomla\Plugin\System\Webauthn\MetadataRepository;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\PublicKeyCredentialSourceRepository;

return new class implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$container->set(
			PluginInterface::class,
			function (Container $container) {
				$config  = (array) PluginHelper::getPlugin('system', 'webauthn');
				$subject = $container->get(DispatcherInterface::class);

				$app     = $container->has(ApplicationInterface::class) ? $container->has(ApplicationInterface::class) : $this->getApplication();
				$session = $container->has('session') ? $container->get('session') : $this->getSession($app);

				$db                    = $container->get('DatabaseDriver');
				$credentialsRepository = $container->has(PublicKeyCredentialSourceRepository::class)
					? $container->get(PublicKeyCredentialSourceRepository::class)
					: new CredentialRepository($db);
				$metadataRepository    = $container->has(MetadataStatementRepository::class)
					? $container->get(MetadataStatementRepository::class)
					: new MetadataRepository;
				$authenticationHelper  = $container->has(Authentication::class)
					? $container->get(Authentication::class)
					: new Authentication($app, $session, $credentialsRepository, $metadataRepository);

				return new Webauthn($subject, $config, $authenticationHelper);
			}
		);
	}

	/**
	 * Get the current CMS application interface.
	 *
	 * @return CMSApplicationInterface|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function getApplication(): ?CMSApplicationInterface
	{
		try
		{
			$app = \Joomla\CMS\Factory::getApplication();
		}
		catch (Exception $e)
		{
			return null;
		}

		return ($app instanceof CMSApplicationInterface) ? $app : null;
	}

	/**
	 * Get the current application session object
	 *
	 * @param   ApplicationInterface  $app  The application we are running in
	 *
	 * @return \Joomla\Session\SessionInterface|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function getSession(?ApplicationInterface $app = null)
	{
		$app = $app ?? $this->getApplication();

		return $app instanceof SessionAwareWebApplicationInterface ? $app->getSession() : null;
	}
};
