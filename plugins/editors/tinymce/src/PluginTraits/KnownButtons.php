<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\PluginTraits;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Returns a list of known TinyMCE buttons.
 *
 * @since  4.1.0
 */
trait KnownButtons
{
    /**
     * Return list of known TinyMCE buttons
     * @link https://www.tiny.cloud/docs/demo/full-featured/
     * @link https://www.tiny.cloud/apps/#core-plugins
     *
     * @return array
     *
     * @since 4.1.0
     */
    public static function getKnownButtons()
    {
        return [
            // General buttons
            '|' => ['label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_SEPARATOR'), 'text' => '|'],

            'undo' => ['label' => 'Undo'],
            'redo' => ['label' => 'Redo'],

            'bold'          => ['label' => 'Bold'],
            'italic'        => ['label' => 'Italic'],
            'underline'     => ['label' => 'Underline'],
            'strikethrough' => ['label' => 'Strikethrough'],
            'styles'        => ['label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_STYLESELECT'), 'text' => 'Formats'],
            'blocks'        => ['label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_FORMATSELECT'), 'text' => 'Paragraph'],
            'fontfamily'    => ['label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_FONTSELECT'), 'text' => 'Font Family'],
            'fontsize'      => ['label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_FONTSIZESELECT'), 'text' => 'Font Sizes'],

            'alignleft'    => ['label' => 'Align left'],
            'aligncenter'  => ['label' => 'Align center'],
            'alignright'   => ['label' => 'Align right'],
            'alignjustify' => ['label' => 'Justify'],
            'lineheight'   => ['label' => 'Line height'],

            'outdent' => ['label' => 'Decrease indent'],
            'indent'  => ['label' => 'Increase indent'],

            'forecolor' => ['label' => 'Text colour'],
            'backcolor' => ['label' => 'Background text colour'],

            'bullist' => ['label' => 'Bullet list'],
            'numlist' => ['label' => 'Numbered list'],

            'link'   => ['label' => 'Insert/edit link', 'plugin' => 'link'],
            'unlink' => ['label' => 'Remove link', 'plugin' => 'link'],

            'subscript'   => ['label' => 'Subscript'],
            'superscript' => ['label' => 'Superscript'],
            'blockquote'  => ['label' => 'Blockquote'],

            'cut'          => ['label' => 'Cut'],
            'copy'         => ['label' => 'Copy'],
            'paste'        => ['label' => 'Paste'],
            'pastetext'    => ['label' => 'Paste as text'],
            'removeformat' => ['label' => 'Clear formatting'],

            'language' => ['label' => 'Language'],

            // Buttons from the plugins
            'accordion'      => ['label' => 'Accordion', 'plugin' => 'accordion'],
            'anchor'         => ['label' => 'Anchor', 'plugin' => 'anchor'],
            'hr'             => ['label' => 'Horizontal line'],
            'ltr'            => ['label' => 'Left to right', 'plugin' => 'directionality'],
            'rtl'            => ['label' => 'Right to left', 'plugin' => 'directionality'],
            'code'           => ['label' => 'Source code', 'plugin' => 'code'],
            'codesample'     => ['label' => 'Insert/Edit code sample', 'plugin' => 'codesample'],
            'table'          => ['label' => 'Table', 'plugin' => 'table'],
            'charmap'        => ['label' => 'Special character', 'plugin' => 'charmap'],
            'visualchars'    => ['label' => 'Show invisible characters', 'plugin' => 'visualchars'],
            'visualblocks'   => ['label' => 'Show blocks', 'plugin' => 'visualblocks'],
            'nonbreaking'    => ['label' => 'Nonbreaking space', 'plugin' => 'nonbreaking'],
            'emoticons'      => ['label' => 'Emoticons', 'plugin' => 'emoticons'],
            'media'          => ['label' => 'Insert/edit video', 'plugin' => 'media'],
            'image'          => ['label' => 'Insert/edit image', 'plugin' => 'image'],
            'pagebreak'      => ['label' => 'Page break', 'plugin' => 'pagebreak'],
            'print'          => ['label' => 'Print'],
            'preview'        => ['label' => 'Preview', 'plugin' => 'preview'],
            'fullscreen'     => ['label' => 'Fullscreen', 'plugin' => 'fullscreen'],
            'jtemplate'      => ['label' => 'Insert template', 'plugin' => 'jtemplate'],
            'searchreplace'  => ['label' => 'Find and replace', 'plugin' => 'searchreplace'],
            'insertdatetime' => ['label' => 'Insert date/time', 'plugin' => 'insertdatetime'],
            'help'           => ['label' => 'Help', 'plugin' => 'help'],
        ];
    }
}
