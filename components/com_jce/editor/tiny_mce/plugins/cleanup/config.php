<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFCleanupPluginConfig
{
    private static $invalid_elements = array('iframe', 'object', 'param', 'embed', 'audio', 'video', 'source', 'script', 'style', 'applet', 'body', 'bgsound', 'base', 'basefont', 'frame', 'frameset', 'head', 'html', 'id', 'ilayer', 'layer', 'link', 'meta', 'name', 'title', 'xml');

    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        // Encoding
        $settings['entity_encoding'] = $wf->getParam('editor.entity_encoding');

        // keep &nbsp;
        $nbsp = (bool) $wf->getParam('editor.keep_nbsp', 1);

        // use named encoding with limited entities set if raw/utf-8 and keep_nbsp === true
        if ($settings['entity_encoding'] === 'raw' && $nbsp) {
            $settings['entity_encoding'] = 'named';
            $settings['entities'] = '160,nbsp,173,shy';
        }

        // set "plugin mode"
        $settings['cleanup_pluginmode'] = $wf->getParam('editor.cleanup_pluginmode', 0, 0);

        // get verify html (default is true)
        $settings['verify_html'] = $wf->getParam('editor.verify_html', 1, 1, 'boolean', false);

        $settings['pad_empty_tags'] = $wf->getParam('editor.pad_empty_tags', 1, 1, 'boolean');

        // set schema
        $settings['schema'] = $wf->getParam('editor.schema', 'mixed', 'mixed');

        if ($settings['schema'] === 'html5') {
            $settings['schema'] = 'html5-strict';
        }

        $settings['validate_styles'] = $wf->getParam('editor.validate_styles', 1, 1, 'boolean', false);

        // Get Extended elements
        $settings['extended_valid_elements'] = $wf->getParam('editor.extended_elements', '', '');
        // Configuration list of invalid elements as array
        $settings['invalid_elements'] = explode(',', preg_replace('#\s+#', '', $wf->getParam('editor.invalid_elements', '', '')));

        // Add elements to invalid list (removed by plugin)
        $settings['invalid_elements'] = array_unique(array_merge($settings['invalid_elements'], self::$invalid_elements));

        // process extended_valid_elements
        if ($settings['extended_valid_elements']) {
            $extended_elements = explode(',', $settings['extended_valid_elements']);

            $elements = array();

            // add wildcard attributes if none specified
            for ($i = 0; $i < count($extended_elements); ++$i) {
                $pos = strpos($extended_elements[$i], '[');

                if ($pos === false) {
                    $elements[] = $extended_elements[$i];
                    $extended_elements[$i] .= '[*]';
                } else {
                    $elements[] = substr($extended_elements[$i], 0, $pos);
                }
            }

            // restore settings to array
            $settings['extended_valid_elements'] = implode(',', $extended_elements);

            if (!empty($elements)) {
                $settings['invalid_elements'] = array_diff($settings['invalid_elements'], $elements);
            }
        }

        // clean invalid_elements
        $settings['invalid_elements'] = array_filter($settings['invalid_elements'], function ($value) {
            return $value !== '';
        });

        // remove it if it is the same as the default
        if ($settings['invalid_elements'] === self::$invalid_elements) {
            $settings['invalid_elements'] = array();
        }

        $settings['invalid_attributes'] = $wf->getParam('editor.invalid_attributes', 'dynsrc,lowsrc', 'dynsrc,lowsrc', 'string', true);
        $settings['invalid_attribute_values'] = $wf->getParam('editor.invalid_attribute_values', '', '', 'string', true);
    }
}
