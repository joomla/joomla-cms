<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFClipboardPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $settings['clipboard_paste_dialog_width'] = $wf->getParam('clipboard.paste_dialog_width', 450, 450);
        $settings['clipboard_paste_dialog_height'] = $wf->getParam('clipboard.paste_dialog_height', 400, 400);
        $settings['clipboard_paste_use_dialog'] = $wf->getParam('clipboard.paste_use_dialog', 0, 0, 'boolean');
        $settings['clipboard_paste_force_cleanup'] = $wf->getParam('clipboard.paste_force_cleanup', 0, 0, 'boolean');
        $settings['clipboard_paste_strip_class_attributes'] = $wf->getParam('clipboard.paste_strip_class_attributes', 2, 2);
        $settings['clipboard_paste_remove_styles'] = $wf->getParam('clipboard.paste_remove_styles', 1, 1, 'boolean');
        $settings['clipboard_paste_retain_style_properties'] = $wf->getParam('clipboard.paste_retain_style_properties', '', '');
        $settings['clipboard_paste_remove_style_properties'] = $wf->getParam('clipboard.paste_remove_style_properties', '', '');
        $settings['clipboard_paste_remove_spans'] = $wf->getParam('clipboard.paste_remove_spans', 0, 0, 'boolean');
        $settings['clipboard_paste_remove_attributes'] = $wf->getParam('clipboard.paste_remove_attributes', '', '');
        $settings['clipboard_paste_remove_styles_if_webkit'] = $wf->getParam('clipboard.paste_remove_styles_if_webkit', 0, 0, 'boolean');
        $settings['clipboard_paste_remove_empty_paragraphs'] = $wf->getParam('clipboard.paste_remove_empty_paragraphs', 1, 1, 'boolean');
        $settings['clipboard_paste_text'] = $wf->getParam('clipboard.paste_text', 1, 1, 'boolean');
        $settings['clipboard_paste_html'] = $wf->getParam('clipboard.paste_html', 1, 1, 'boolean');

        $settings['clipboard_paste_process_footnotes'] = $wf->getParam('clipboard.paste_process_footnotes', 'convert', 'convert');
        $settings['clipboard_paste_upload_images'] = $wf->getParam('clipboard.paste_upload_images', 0, 0);

        $settings['clipboard_paste_remove_tags'] = $wf->getParam('clipboard.paste_remove_tags', '', '');
        $settings['clipboard_paste_keep_tags'] = $wf->getParam('clipboard.paste_keep_tags', '', '');
        $settings['clipboard_paste_filter'] = $wf->getParam('clipboard.paste_filter', '', '');
    }
}
