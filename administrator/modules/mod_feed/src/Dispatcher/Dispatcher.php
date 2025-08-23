<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Feed\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_feed
 *
 * @since  5.3.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.3.0
     */
    protected function getLayoutData()
    {
        $data        = parent::getLayoutData();
        $feedFactory = new FeedFactory();

        $data['feed']   = $this->getHelperFactory()->getHelper('FeedHelper')->getFeedData($data['params'], $feedFactory);
        $data['rssurl'] = $data['params']->get('rssurl', '');
        $data['rssrtl'] = $data['params']->get('rssrtl', 0);

        return $data;
    }
}
