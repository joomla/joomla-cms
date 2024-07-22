<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Syndicate\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_syndicate
 *
 * @since  5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function dispatch()
    {
        $displayData = $this->getLayoutData();

        if ($displayData['link'] === null) {
            return;
        }

        parent::dispatch();
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['params']->def('format', 'rss');

        $data['link'] = $this->getHelperFactory()->getHelper('SyndicateHelper')->getSyndicateLink($data['params'], $this->getApplication()->getDocument());
        $data['text'] = htmlspecialchars($data['params']->get('text', ''), ENT_COMPAT, 'UTF-8');

        return $data;
    }
}
