<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.none
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;

/**
 * Plain Textarea Editor Plugin
 *
 * @since  1.5
 */
class PlgEditorNone extends CMSPlugin
{
    /**
     * Display the editor area.
     *
     * @param   string   $name     The control name.
     * @param   string   $content  The contents of the text area.
     * @param   string   $width    The width of the text area (px or %).
     * @param   string   $height   The height of the text area (px or %).
     * @param   integer  $col      The number of columns for the textarea.
     * @param   integer  $row      The number of rows for the textarea.
     * @param   boolean  $buttons  True and the editor buttons will be displayed.
     * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
     * @param   string   $asset    The object asset
     * @param   object   $author   The author.
     * @param   array    $params   Associative array of editor parameters.
     *
     * @return  string
     */
    public function onDisplay(
        $name,
        $content,
        $width,
        $height,
        $col,
        $row,
        $buttons = true,
        $id = null,
        $asset = null,
        $author = null,
        $params = array()
    ) {
        if (empty($id)) {
            $id = $name;
        }

        // Only add "px" to width and height if they are not given as a percentage
        if (is_numeric($width)) {
            $width .= 'px';
        }

        if (is_numeric($height)) {
            $height .= 'px';
        }

        $readonly = !empty($params['readonly']) ? ' readonly disabled' : '';

        Factory::getDocument()->getWebAssetManager()
            ->registerAndUseScript(
                'webcomponent.editor-none',
                'plg_editors_none/joomla-editor-none.min.js',
                [],
                ['type' => 'module']
            );

        return '<joomla-editor-none>'
            . '<textarea name="' . $name . '" id="' . $id . '" cols="' . $col . '" rows="' . $row
            . '" style="width: ' . $width . '; height: ' . $height . ';"' . $readonly . '>' . $content . '</textarea>'
            . '</joomla-editor-none>'
            . $this->_displayButtons($id, $buttons, $asset, $author);
    }

    /**
     * Displays the editor buttons.
     *
     * @param   string  $name     The control name.
     * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
     * @param   string  $asset    The object asset
     * @param   object  $author   The author.
     *
     * @return  void|string HTML
     */
    public function _displayButtons($name, $buttons, $asset, $author)
    {
        if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
            $buttonsEvent = new Event(
                'getButtons',
                [
                    'editor'    => $name,
                    'buttons' => $buttons,
                ]
            );

            $buttonsResult = $this->getDispatcher()->dispatch('getButtons', $buttonsEvent);
            $buttons       = $buttonsResult['result'];

            return LayoutHelper::render('joomla.editors.buttons', $buttons);
        }
    }
}
