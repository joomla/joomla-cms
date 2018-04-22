<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Association\AssociationServiceTrait;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\Component;
use Joomla\CMS\HTML\HTMLRegistryAwareInterface;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\Component\Content\Administrator\Service\HTML\AdministratorService;
use Joomla\Component\Content\Administrator\Service\HTML\Icon;

/**
 * Component class for com_content
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentComponent extends Component implements BootableExtensionInterface, AssociationServiceInterface, HTMLRegistryAwareInterface
{
	use AssociationServiceTrait;
	use HTMLRegistryAwareTrait;

	/**
	 * Booting the extension.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function boot()
	{
		$this->getRegistry()->register('contentadministrator', new AdministratorService);
		$this->getRegistry()->register('contenticon', new Icon);

		// The layout joomla.content.icons does need a general icon service
		$this->getRegistry()->register('icon', $this->getRegistry()->getService('contenticon'));
	}
}
