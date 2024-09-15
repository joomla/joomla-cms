<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Media\Administrator\Provider\ProviderManagerHelperTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media View Model
 *
 * @since  4.0.0
 */
class MediaModel extends BaseDatabaseModel
{
    use ProviderManagerHelperTrait;

    /**
     * Obtain list of supported providers
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function getProviders()
    {
        $results = [];

        foreach ($this->getProviderManager()->getProviders() as $provider) {
            $result               = new \stdClass();
            $result->name         = $provider->getID();
            $result->displayName  = $provider->getDisplayName();
            $result->adapterNames = [];

            foreach ($provider->getAdapters() as $adapter) {
                $result->adapterNames[] = $adapter->getAdapterName();
            }

            $results[] = $result;
        }

        return $results;
    }
}
