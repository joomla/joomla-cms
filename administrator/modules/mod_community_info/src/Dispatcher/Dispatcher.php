<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_community_info
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\CommunityInfo\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_community_info
 *
 * @since  4.5.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   4.5.0
     */
    public function dispatch()
    {
        // The community info will not show if no user is logged in.
        $user = $this->getApplication()->getIdentity();

        if ($user === null || $user->id === 0) {
            return;
        }

        /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wr = $wa->getRegistry();
        $wr->addRegistryFile('media/mod_community_info/joomla.asset.json');

        parent::dispatch();
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   4.5.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        // Initialize the helper class
        $helper = $this->getHelperFactory()->getHelper('CommunityInfoHelper', [$this->module->id, $data['params']]);

        // Update location by form values
        $task = $this->input->getCmd('module_task', '');

        if (\in_array($task, ['saveLocation', 'autoLocation'])) {
            $helper->setLocationForm($task);
        }

        // Get links and location
        $data['links']            = $helper->getLinks();
        $data['currentLoc']       = $helper->getLocation('geolocation');
        $data['currentLocLabel']  = $helper->getLocation('label');
        $data['currentLocArrary'] = explode(',', $data['currentLoc'], 2);

        // Fetch news feed
        if ($data['links']->exists('news_feed')) {
            $data['news'] = $helper->getNewsFeed($data['links']->get('news_feed'), 3);
        }

        // Fetch evets feed
        if ($data['links']->exists('events_feed')) {
            $data['events'] = $helper->getEventsFeed($data['links']->get('events_feed'), 3);
        }

        return $data;
    }
}
