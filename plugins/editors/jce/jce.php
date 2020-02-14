<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
// Do not allow direct access
defined('JPATH_PLATFORM') or die;

/**
 * JCE WYSIWYG Editor Plugin.
 *
 * @since 1.5
 */
class plgEditorJCE extends JPlugin
{
    /**
     * Constructor.
     *
     * @param object $subject The object to observe
     * @param array  $config  An array that holds the plugin configuration
     *
     * @since       1.5
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
    }

    /**
     * Method to handle the onInit event.
     *  - Initializes the JCE WYSIWYG Editor.
     *
     * @param   $toString Return javascript and css as a string
     *
     * @return string JavaScript Initialization string
     *
     * @since   1.5
     */
    public function onInit()
    {
        $app = JFactory::getApplication();
        $language = JFactory::getLanguage();

        $document = JFactory::getDocument();

        $language->load('plg_editors_jce', JPATH_ADMINISTRATOR);
        $language->load('com_jce', JPATH_ADMINISTRATOR);

        $app->triggerEvent('onBeforeWfEditorLoad');

        // load base file
        require_once JPATH_ADMINISTRATOR . '/components/com_jce/includes/base.php';

        // create editor
        $editor = new WFEditor();
        $settings = $editor->getSettings();

        $app->triggerEvent('onBeforeWfEditorRender', array(&$settings));

        $editor->render($settings);

        // get media version
        $version = $document->getMediaVersion();

        foreach ($editor->getScripts() as $script) {
            // add version directly for backwards compatablity
            if (strpos($script, '?') === false) {
                $script .= '?' . $version;
            } else {
                $script .= '&' . $version;
            }

            $document->addScript($script);
        }

        foreach ($editor->getStyleSheets() as $style) {
            // add version directly for backwards compatablity
            if (strpos($style, '?') === false) {
                $style .= '?' . $version;
            } else {
                $style .= '&' . $version;
            }
            
            $document->addStylesheet($style);
        }

        $document->addScriptDeclaration(implode("\n", $editor->getScriptDeclaration()));
    }

    /**
     * JCE WYSIWYG Editor - get the editor content.
     *
     * @vars string   The name of the editor
     */
    public function onGetContent($editor)
    {
        return $this->onSave($editor);
    }

    /**
     * JCE WYSIWYG Editor - set the editor content.
     *
     * @vars string   The name of the editor
     */
    public function onSetContent($editor, $html)
    {
        return "WFEditor.setContent('" . $editor . "','" . $html . "');";
    }

    /**
     * JCE WYSIWYG Editor - copy editor content to form field.
     *
     * @vars string   The name of the editor
     */
    public function onSave($editor)
    {
        return "WFEditor.getContent('" . $editor . "');";
    }

    /**
     * JCE WYSIWYG Editor - display the editor.
     *
     * @vars string The name of the editor area
     * @vars string The content of the field
     * @vars string The width of the editor area
     * @vars string The height of the editor area
     * @vars int The number of columns for the editor area
     * @vars int The number of rows for the editor area
     * @vars mixed Can be boolean or array.
     */
    public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null)
    {
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

        if (empty($id)) {
            $id = $name;
        }
        $editor = '<div class="editor wf-editor-container mb-2">';
        $editor .= '  <div class="wf-editor-header"></div>';
        $editor .= '  <textarea spellcheck="false" id="' . $id . '" name="' . $name . '" cols="' . $col . '" rows="' . $row . '" style="width:' . $width . ';height:' . $height . ';" class="wf-editor mce_editable" wrap="off">' . $content . '</textarea>';
        $editor .= '</div>';
        $editor .= $this->displayButtons($id, $buttons, $asset, $author);

        return $editor;
    }

    public function onGetInsertMethod($name)
    {
    }

    private function displayButtons($name, $buttons, $asset, $author)
    {
        $return = '';

        if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
            $buttonsEvent = new Joomla\Event\Event(
                'getButtons',
                [
                    'editor' => $name,
                    'buttons' => $buttons,
                ]
            );

            if (method_exists($this, 'getDispatcher')) {
                $buttonsResult = $this->getDispatcher()->dispatch('getButtons', $buttonsEvent);
                $buttons = $buttonsResult['result'];
            } else {
                $buttons = $this->_subject->getButtons($name, $buttons, $asset, $author);
            }

            // fix some legacy buttons
            array_walk($buttons, function($button) {
                $cls = $button->get('class', '');

                if (empty($cls) || strpos($cls, 'btn') === false) {
                    $cls .= ' btn';
                    $button->set('class', trim($cls));
                }
            });

            return JLayoutHelper::render('joomla.editors.buttons', $buttons);
        }
    }
}
