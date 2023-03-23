<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.readmore
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor Readmore button
 *
 * @since  1.5
 */
class PlgButtonReadmore extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     */
    protected $app;

    /**
     * Readmore button
     *
     * @param   string  $name  The name of the button to add
     *
     * @return  CMSObject  $button  A two element array of (imageName, textToInsert)
     *
     * @since   1.5
     */
    public function onDisplay($name)
    {
        $doc = $this->app->getDocument();
        $doc->getWebAssetManager()
            ->registerAndUseScript('com_content.admin-article-readmore', 'com_content/admin-article-readmore.min.js', [], ['defer' => true], ['core']);

        // Pass some data to javascript
        $doc->addScriptOptions(
            'xtd-readmore',
            [
                'exists' => Text::_('PLG_READMORE_ALREADY_EXISTS', true),
            ]
        );

        $button = new CMSObject();
        $button->modal   = false;
        $button->onclick = 'insertReadmore(\'' . $name . '\');return false;';
        $button->text    = Text::_('PLG_READMORE_BUTTON_READMORE');
        $button->name    = $this->_type . '_' . $this->_name;
        $button->icon    = 'arrow-down';
        $button->iconSVG = '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M32 12l-6-6-10 10-10-10-6 6 16 16z"></path></svg>';
        $button->link    = '#';

        return $button;
    }
}
