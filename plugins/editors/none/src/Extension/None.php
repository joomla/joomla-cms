<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.none
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\None\Extension;

use Joomla\CMS\Event\Editor\EditorSetupEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Editors\None\Provider\EditorNoneProvider;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plain Textarea Editor Plugin
 *
 * @since  1.5
 */
final class None extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onEditorSetup' => 'onEditorSetup',
        ];
    }

    /**
     * Register Editor instance
     *
     * @param EditorSetupEvent $event
     *
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onEditorSetup(EditorSetupEvent $event): void
    {
        $event->getEditorsRegistry()
            ->add(new EditorNoneProvider($this->params, $this->getApplication(), $this->getDispatcher()));
    }
}
