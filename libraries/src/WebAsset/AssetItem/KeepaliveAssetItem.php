<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset\AssetItem;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface;
use Joomla\CMS\WebAsset\WebAssetItem;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Asset Item class for Keepalive asset
 *
 * @since  4.0.0
 */
class KeepaliveAssetItem extends WebAssetItem implements WebAssetAttachBehaviorInterface
{
    /**
     * Method called when asset attached to the Document.
     * Useful for Asset to add a Script options.
     *
     * @param   Document  $doc  Active document
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function onAttachCallback(Document $doc)
    {
        $app            = Factory::getApplication();
        $sessionHandler = $app->get('session_handler', 'database');

        // If the handler is not 'Database', we set a fixed, small refresh value (here: 5 min)
        $refreshTime = 300;

        if ($sessionHandler === 'database') {
            $lifeTime    = $app->getSession()->getExpire();
            $refreshTime = $lifeTime <= 60 ? 45 : $lifeTime - 60;

            // The longest refresh period is one hour to prevent integer overflow.
            if ($refreshTime > 3600 || $refreshTime <= 0) {
                $refreshTime = 3600;
            }
        }

        // If we are in the frontend or logged in as a user, we can use the ajax component to reduce the load
        $uri = 'index.php' . ($app->isClient('site') || !Factory::getUser()->guest ? '?option=com_ajax&format=json' : '');

        // Add keepalive script options.
        $doc->addScriptOptions('system.keepalive', ['interval' => $refreshTime * 1000, 'uri' => Route::_($uri)]);
    }
}
