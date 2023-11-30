<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\CodeMirror\Extension;

use Joomla\CMS\Event\Editor\EditorSetupEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Editors\CodeMirror\Provider\CodeMirrorProvider;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * CodeMirror Editor Plugin.
 *
 * @since  1.6
 */
final class Codemirror extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onEditorSetup' => 'onEditorSetup'];
    }

    /**
     * Register Editor instance
     *
     * @param EditorSetupEvent $event
     *
     * @return void
     *
     * @since   5.0.0
     */
    public function onEditorSetup(EditorSetupEvent $event)
    {
        $this->loadLanguage();

        $event->getEditorsRegistry()
            ->add(new CodeMirrorProvider($this->params, $this->getApplication(), $this->getDispatcher()));
    }
}
