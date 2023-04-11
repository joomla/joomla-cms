<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesPopular\Site\Dispatcher;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_articles_popular
 *
 * @since  4.3.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   4.3.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        if (!ComponentHelper::getParams('com_content')->get('record_hits', 1)) {
            $data['hitsDisabledMessage'] = Text::_('JGLOBAL_RECORD_HITS_DISABLED');
        } else {
            $data['list'] = $this->getHelperFactory()->getHelper('ArticlesPopularHelper', $data)->getArticles($data['params'], $data['app']);
        }

        return $data;
    }
}
