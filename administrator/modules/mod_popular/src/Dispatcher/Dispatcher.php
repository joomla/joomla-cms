<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Popular\Administrator\Dispatcher;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Module\Popular\Administrator\Helper\PopularHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_popular
 *
 * @since  6.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   6.0.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();
        /** @var PopularHelper $helper */
        $helper       = $this->getHelperFactory()->getHelper('PopularHelper', $data);
        $articleModel = $this
            ->getApplication()
            ->bootComponent('com_content')
            ->getMVCFactory()
            ->createModel('Articles', 'Administrator', ['ignore_request' => true]);

        if ($data['params']->get('automatic_title', 0)) {
            $data['module']->title = $helper->getModuleTitle($data['params']);
        }

        $data['list']        = $helper->getArticles($data['params'], $articleModel);
        $data['record_hits'] = (int) ComponentHelper::getParams('com_content')->get('record_hits', 1);

        return $data;
    }
}
