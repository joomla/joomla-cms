<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Finder\Site\Dispatcher;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;
use Joomla\Component\Finder\Site\Helper\RouteHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_finder
 *
 * @since  5.4.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.4.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $cparams = ComponentHelper::getParams('com_finder');

        // Check for OpenSearch
        if ($data['params']->get('opensearch', $cparams->get('opensearch', 1))) {
            $defaultTitle = Text::_('MOD_FINDER_OPENSEARCH_NAME') . ' ' . $data['app']->get('sitename');
            $ostitle      = $data['params']->get('opensearch_name', $cparams->get('opensearch_name', $defaultTitle));
            $data['app']->getDocument()->addHeadLink(
                Uri::getInstance()->toString(['scheme', 'host', 'port']) . Route::_('index.php?option=com_finder&view=search&format=opensearch'),
                'search',
                'rel',
                ['title' => $ostitle, 'type' => 'application/opensearchdescription+xml']
            );
        }

        // Get the route.
        $data['route'] = RouteHelper::getSearchRoute($data['params']->get('searchfilter', null));

        if ($data['params']->get('set_itemid')) {
            $uri = Uri::getInstance($data['route']);
            $uri->setVar('Itemid', $data['params']->get('set_itemid'));
            $data['route'] = $uri->toString(['path', 'query']);
        }

        // Load component language file.
        LanguageHelper::loadComponentLanguage();

        // Load plugin language files.
        LanguageHelper::loadPluginLanguage();

        // Get Smart Search query object.
        $data['query'] = $this->getHelperFactory()->getHelper('FinderHelper')->getSearchQuery($data['params'], $data['app']);

        return $data;
    }
}
