<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\ViewTraits;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Model\Profiles;
use Akeeba\Engine\Platform;

trait ProfileIdAndName
{
	/**
	 * Active profile ID
	 *
	 * @var  int
	 */
	public $profileId = 0;

	/**
	 * Active profile's description
	 *
	 * @var  string
	 */
	public $profileName = '';

	/**
	 * Is this profile available as an One Click Backup icon? 0/1
	 *
	 * @var  int
	 */
	public $quickIcon = 0;

	/**
	 * Find the currently active profile ID and name and put them in properties accessible by the view template
	 */
	protected function getProfileIdAndName()
	{
		/** @var Profiles $profilesModel */
		$profilesModel = $this->container->factory->model('Profiles')->tmpInstance();
		$profileId     = Platform::getInstance()->get_active_profile();

		try
		{
			$this->profileName = $profilesModel->findOrFail($profileId)->description;
			$this->profileId   = $profileId;
			$this->quickIcon   = $profilesModel->quickicon;
		}
		catch (\Exception $e)
		{
			$this->container->platform->setSessionVar('profile', 1, 'akeeba');

			$this->profileId   = 1;
			$this->profileName = $profilesModel->findOrFail(1)->description;
		}
	}
}
