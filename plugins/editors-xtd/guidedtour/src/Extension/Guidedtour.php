<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.guidedtour
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\EditorsXtd\Guidedtour\Extension;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor Guidedtour button
 *
 * @since  __DEPLOY_VERSION__
 */
final class Guidedtour extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Display the button.
     *
     * @param   string   $name    The name of the button to display.
     * @param   string   $asset   The name of the asset being edited.
     * @param   integer  $author  The id of the author owning the asset being edited.
     *
     * @return  CMSObject|false
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onDisplay($name)
    {
        $user  = $this->getApplication()->getIdentity();

        // Can create guided tours
        $canCreateRecords = $user->authorise('core.create', 'com_guidedtours');

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values           = (array) $this->getApplication()->getUserState('com_guidedtours.edit.tour.id');
        $isEditingRecords = count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return false;
        }

        $itemId = $this->getApplication()->getInput()->getInt('id',0);
        if ($itemId > 0)
        {
            $model = $this->getApplication()->bootComponent('com_modules')
                          ->getMVCFactory()->createModel('Module', 'Administrator');

            $item     = $model->getItem();
            if (!$item)
            {
                return false;
            }
            $clientId = $item->client_id;
        } else
        {
            $clientId = $this->getApplication()->getInput()->getInt( 'client_id', 0 );
        }

        // administrator modules only
        if ($clientId === 0)
        {
            return false;
        }

        $link = 'index.php?option=com_guidedtours&amp;view=tours&amp;layout=modal&amp;tmpl=component&amp;'
                . Session::getFormToken() . '=1&amp;editor=' . $name;

        $button          = new CMSObject();
        $button->modal   = true;
        $button->link    = $link;
        $button->text    = Text::_('PLG_EDITORS-XTD_GUIDEDTOUR_BUTTON_TOUR');
        $button->name    = $this->_type . '_' . $this->_name;
        $button->icon    = 'icon-map-signs';
        $button->iconSVG = '<svg viewBox="0 0 512 512" width="24" height="24" focusable="false">'
. '<path d="M224 32H64C46.3 32 32 46.3 32 64v6'
. '4c0 17.7 14.3 32 32 32H441.4c4.2 0 8.3-1.7 11.3-4.7l48-48c6.2-6.2 6.2-16.4 0-22'
. '.6l-48-48c-3-3-7.1-4.7-11.3-4.7H288c0-17.7-14.3-32-32-32s-32 14.3-32 32zM480 25'
. '6c0-17.7-14.3-32-32-32H288V192H224v32H70.6c-4.2 0-8.3 1.7-11.3 4.7l-48 48c-6.2 '
. '6.2-6.2 16.4 0 22.6l48 48c3 3 7.1 4.7 11.3 4.7H448c17.7 0 32-14.3 32-32V256zM28'
. '8 480V384H224v96c0 17.7 14.3 32 32 32s32-14.3 32-32z"/></svg>';
        $button->options = [
            'height'     => '300px',
            'width'      => '800px',
            'bodyHeight' => '70',
            'modalWidth' => '80',
        ];

        return $button;
    }
}
