<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.externallink
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Externallink\Extension;

use Joomla\CMS\Event\Application\BeforeCompileHeadEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Adds an accessible external-window indicator to links opening a new tab or window.
 *
 * @since  __DEPLOY_VERSION__
 */
final class Externallink extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns the events this plugin subscribes to.
     *
     * @return  array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead' => 'onBeforeCompileHead',
        ];
    }

    /**
     * Load the assets on the administrator and, optionally, site client.
     */
    public function onBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        $app = $event->getApplication();

        if (!$app->isClient('administrator') && !($app->isClient('site') && (int) $this->params->get('enable_frontend', 0) === 1)) {
            return;
        }

        $document = $event->getDocument();

        if ($document->getType() !== 'html') {
            return;
        }

        $this->loadLanguage();
        Text::script('PLG_SYSTEM_EXTERNALLINK_NEW_WINDOW');

        $webAssetManager = $document->getWebAssetManager();

        $webAssetManager
            ->registerAndUseScript(
                'plg_system_externallink',
                'plg_system_externallink/externallink.js',
                [],
                ['defer' => true]
            )
            ->registerAndUseStyle(
                'plg_system_externallink.style',
                'plg_system_externallink/externallink.css'
            );
    }
}
