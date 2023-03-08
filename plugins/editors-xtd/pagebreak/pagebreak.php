<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.pagebreak
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor Pagebreak button
 *
 * @since  1.5
 */
class PlgButtonPagebreak extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Display the button
     *
     * @param   string  $name  The name of the button to add
     *
     * @return  CMSObject|void  The button options as CMSObject
     *
     * @since   1.5
     */
    public function onDisplay($name)
    {
        $user  = Factory::getUser();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
        $isEditingRecords = count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return;
        }

        Factory::getDocument()->addScriptOptions('xtd-pagebreak', ['editor' => $name]);
        $link = 'index.php?option=com_content&amp;view=article&amp;layout=pagebreak&amp;tmpl=component&amp;e_name=' . $name;

        $button = new CMSObject();
        $button->modal   = true;
        $button->link    = $link;
        $button->text    = Text::_('PLG_EDITORSXTD_PAGEBREAK_BUTTON_PAGEBREAK');
        $button->name    = $this->_type . '_' . $this->_name;
        $button->icon    = 'copy';
        $button->iconSVG = '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M26 8h-6v-2l-6-6h-14v24h12v8h20v-18l-6-6zM26 10.828l3.172 3'
                            . '.172h-3.172v-3.172zM14 2.828l3.172 3.172h-3.172v-3.172zM2 2h10v6h6v14h-16v-20zM30 30h-16v-6h6v-14h4v6h6v14z"></pa'
                            . 'th></svg>';
        $button->options = [
            'height'     => '200px',
            'width'      => '400px',
            'bodyHeight' => '70',
            'modalWidth' => '80',
        ];

        return $button;
    }
}
