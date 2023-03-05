<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\CodeMirror\Extension;

use Joomla\CMS\Editor\EditorRegistry;
use Joomla\CMS\Event\Editor\EditorSetupEvent;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
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
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public function onEditorSetup(EditorSetupEvent $event)
    {
        $this->loadLanguage();

        /** @var EditorRegistry $subject */
        $subject = $event['subject'];
        $subject->add(new CodeMirrorProvider($this->params, $this->getApplication()));
    }

    /**
     * Displays the editor buttons.
     *
     * @param   string  $name     Button name.
     * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
     * @param   mixed   $asset    Unused.
     * @param   mixed   $author   Unused.
     *
     * @return  string|void
     */
    protected function displayButtons($name, $buttons, $asset, $author)
    {
        if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
            $buttonsEvent = new Event(
                'getButtons',
                [
                    'editor'  => $name,
                    'buttons' => $buttons,
                ]
            );

            $buttonsResult = $this->getDispatcher()->dispatch('getButtons', $buttonsEvent);
            $buttons       = $buttonsResult['result'];

            return LayoutHelper::render('joomla.editors.buttons', $buttons);
        }
    }
}
