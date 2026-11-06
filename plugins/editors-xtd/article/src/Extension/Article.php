<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.article
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\EditorsXtd\Article\Extension;

use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor Article button
 *
 * @since  1.5
 */
final class Article extends CMSPlugin implements SubscriberInterface
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
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    /**
     * @param  EditorButtonsSetupEvent $event
     * @return void
     *
     * @since   5.0.0
     */
    public function onEditorButtonsSetup(EditorButtonsSetupEvent $event): void
    {
        $subject  = $event->getButtonsRegistry();
        $disabled = $event->getDisabledButtons();

        if (\in_array($this->_name, $disabled)) {
            return;
        }

        $this->loadLanguage();

        $button = $this->onDisplay($event->getEditorId());

        if ($button) {
            $subject->add($button);
        }
    }

    /**
     * Display the button
     *
     * @param   string  $name  The name of the button to add
     *
     * @return  Button|void  The button options as Button object, void if ACL check fails.
     *
     * @since   1.5
     *
     * @deprecated  5.0 Use onEditorButtonsSetup event instead, will be removed in 7.0
     */
    public function onDisplay($name)
    {
        $user  = $this->getApplication()->getIdentity();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || \count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values           = (array) $this->getApplication()->getUserState('com_content.edit.article.id');
        $isEditingRecords = \count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return;
        }

        $link = 'index.php?option=com_content&view=articles&layout=modal&tmpl=component&'
            . Session::getFormToken() . '=1&editor=' . $name;

        $button = new Button(
            $this->_name,
            [
                'action'  => 'modal',
                'link'    => $link,
                'text'    => Text::_('PLG_ARTICLE_BUTTON_ARTICLE'),
                'icon'    => 'copy',
                'iconSVG' => '<svg viewBox="0 0 448 512" width="24" height="24"><path d="M208 0L332.1 0c12.7 0 24.9 5.1 33.9 14.1l67.9 67.9c9 9 14.1 21.2 14.1 33.9'
                . 'L448 336c0 26.5-21.5 48-48 48l-192 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48zM48 128l80 0 0 64-64 0 0 256 192 0 0-32 64 0 0 48c0 26.5'
                . '-21.5 48-48 48L48 512c-26.5 0-48-21.5-48-48L0 176c0-26.5 21.5-48 48-48z"/></svg>',
                // This is whole Plugin name, it is needed for keeping backward compatibility
                'name' => $this->_type . '_' . $this->_name,
            ]
        );

        // Register as a link source for editors that offer a link picker (e.g. TinyMCE)
        $this->getApplication()->getDocument()->addScriptOptions('editor-link-providers', [
            $this->_name => [
                'title'  => Text::_('PLG_ARTICLE_BUTTON_ARTICLE'),
                'icon'   => 'copy',
                'src'    => 'index.php?option=com_content&view=articles&layout=modal&tmpl=component',
                'select' => 'content',
            ],
        ], true);

        return $button;
    }
}
