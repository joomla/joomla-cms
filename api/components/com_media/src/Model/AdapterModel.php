<?php

/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\Model;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Component\Media\Administrator\Provider\ProviderManagerHelperTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media web service model supporting a single adapter item.
 *
 * @since  4.1.0
 */
class AdapterModel extends BaseModel
{
    use ProviderManagerHelperTrait;

    /**
     * Method to get a single adapter.
     *
     * @return  \stdClass  The adapter.
     *
     * @since   4.1.0
     */
    public function getItem(): \stdClass
    {
        [$provider, $account] = array_pad(explode('-', $this->getState('id'), 2), 2, null);

        if ($account === null) {
            throw new \Exception('Account was not set');
        }

        $provider = $this->getProvider($provider);
        $adapter  = $this->getAdapter($this->getState('id'));

        $obj              = new \stdClass();
        $obj->id          = $provider->getID() . '-' . $adapter->getAdapterName();
        $obj->provider_id = $provider->getID();
        $obj->name        = $adapter->getAdapterName();
        $obj->path        = $provider->getID() . '-' . $adapter->getAdapterName() . ':/';

        return $obj;
    }
}
