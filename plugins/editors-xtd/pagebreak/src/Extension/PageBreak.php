<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.pagebreak
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\EditorsXtd\PageBreak\Extension;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor Pagebreak button
 *
 * @since  1.5
 */
final class PageBreak extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    /**
     * @param  EditorButtonsSetupEvent $event
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onEditorButtonsSetup(EditorButtonsSetupEvent $event): void
    {
        $subject  = $event->getButtonsRegistry();
        $disabled = $event->getDisabledButtons();

        if (\in_array($this->_name, $disabled)) {
            return;
        }

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
     * @return  Button|void  The button options as Button object
     *
     * @since   1.5
     *
     * @deprecated  5.0 Use onEditorButtonsSetup event
     */
    public function onDisplay($name)
    {
        $app = $this->getApplication();

        if (!$app instanceof CMSWebApplicationInterface) {
            return;
        }

        $user = $app->getIdentity();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || \count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values           = (array) $app->getUserState('com_content.edit.article.id');
        $isEditingRecords = \count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return;
        }

        $this->loadLanguage();
        $app->getDocument()->addScriptOptions('xtd-pagebreak', ['editor' => $name]);
        $link = 'index.php?option=com_content&view=article&layout=pagebreak&tmpl=component&e_name=' . $name;

        $button = new Button(
            $this->_name,
            [
                'action'  => 'modal',
                'link'    => $link,
                'text'    => Text::_('PLG_EDITORSXTD_PAGEBREAK_BUTTON_PAGEBREAK'),
                'icon'    => 'copy',
                'iconSVG' => '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M26 8h-6v-2l-6-6h-14v24h12v8h20v-18l-6-6zM26 10.828l3.172 3'
                    . '.172h-3.172v-3.172zM14 2.828l3.172 3.172h-3.172v-3.172zM2 2h10v6h6v14h-16v-20zM30 30h-16v-6h6v-14h4v6h6v14z"></pa'
                    . 'th></svg>',
                // This is whole Plugin name, it is needed for keeping backward compatibility
                'name' => $this->_type . '_' . $this->_name,
            ]
        );

        return $button;
    }
}
