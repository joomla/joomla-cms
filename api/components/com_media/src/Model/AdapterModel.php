<?php
/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;

/**
 * Media web service model supporting a single adapter item.
 *
 * @since  __DEPLOY_VERSION__
 */
class AdapterModel extends BaseModel
{
	/**
	 * Method to get a single adapter.
	 *
	 * @return  \stdClass  The adapter.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem(): \stdClass
	{
		$providerManager = new ProviderManager;

		// Fire the event to get the results
		$eventParameters = ['context' => 'AdapterManager', 'providerManager' => $providerManager];
		$event           = new MediaProviderEvent('onSetupProviders', $eventParameters);
		PluginHelper::importPlugin('filesystem');
		Factory::getApplication()->getDispatcher()->dispatch('onSetupProviders', $event);

		list($provider, $account) = array_pad(explode('-', $this->getState('id'), 2), 2, null);

		if ($account === null)
		{
			throw new \Exception('Account was not set');
		}

		$provider = $providerManager->getProvider($provider);
		$adapter  = $providerManager->getAdapter($this->getState('id'));

		$obj              = new \stdClass;
		$obj->id          = $provider->getID() . '-' . $adapter->getAdapterName();
		$obj->provider_id = $provider->getID();
		$obj->name        = $adapter->getAdapterName();
		$obj->path        = $provider->getID() . '-' . $adapter->getAdapterName() . ':/';

		return $obj;
	}
}
