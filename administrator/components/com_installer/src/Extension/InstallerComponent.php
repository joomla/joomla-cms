<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Extension;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\Component\Installer\Administrator\Service\HTML\Manage;
use Joomla\Component\Installer\Administrator\Service\HTML\Updatesites;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_installer
 *
 * @since  4.0.0
 */
class InstallerComponent extends MVCComponent implements
	BootableExtensionInterface
{
	use HTMLRegistryAwareTrait;

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function boot(ContainerInterface $container)
	{
		$this->getRegistry()->register('manage', new Manage);
		$this->getRegistry()->register('updatesites', new Updatesites);
	}
}
