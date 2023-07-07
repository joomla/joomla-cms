<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.guidedtour
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\EditorsXtd\Guidedtour\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor Guidedtour button
 *
 * @since  1.5
 */
final class Guidedtour extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
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
     * @since   1.5
     */
    public function onDisplay($name)
    {

        $button          = new CMSObject();
        $button->modal   = false;
        $button->onclick = 'Joomla.editors.instances[\'' . $name . '\'].replaceSelection(\'<hr id="system-readmore">\');return false;';
        $button->text    = Text::_('PLG_READMORE_BUTTON_READMORE');
        $button->name    = $this->_type . '_' . $this->_name;
        $button->icon    = 'arrow-down';
        $button->iconSVG = '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M32 12l-6-6-10 10-10-10-6 6 16 16z"></path></svg>';
        $button->link    = '#';

        return $button;
    }
}
