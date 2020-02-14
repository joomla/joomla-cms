<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFFormatselectPluginConfig
{
    protected static $formats = array(
        'p' => 'advanced.paragraph',
        'address' => 'advanced.address',
        'pre' => 'advanced.pre',
        'h1' => 'advanced.h1',
        'h2' => 'advanced.h2',
        'h3' => 'advanced.h3',
        'h4' => 'advanced.h4',
        'h5' => 'advanced.h5',
        'h6' => 'advanced.h6',
        'div' => 'advanced.div',
        'div_container' => 'advanced.div_container',
        'blockquote' => 'advanced.blockquote',
        'code' => 'advanced.code',
        'samp' => 'advanced.samp',
        'span' => 'advanced.span',
        'section' => 'advanced.section',
        'article' => 'advanced.article',
        'aside' => 'advanced.aside',
        'header' => 'advanced.header',
        'footer' => 'advanced.footer',
        'nav' => 'advanced.nav',
        'figure' => 'advanced.figure',
        //'figcaption' => 'advanced.figcaption',
        'dt' => 'advanced.dt',
        'dd' => 'advanced.dd',
    );

    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        // html5 block elements
        $html5 = array('section', 'article', 'aside', 'figure');
        // get current schema
        $schema = $wf->getParam('editor.schema', 'html4');
        $verify = (bool) $wf->getParam('editor.verify_html', 0);

        $legacy = $wf->getParam('editor.theme_advanced_blockformats');
        $default = 'p,div,address,pre,h1,h2,h3,h4,h5,h6,code,samp,span,section,article,aside,header,footer,nav,figure,dt,dd';

        // get blockformats from parameter
        $blockformats = $wf->getParam('formatselect.blockformats');

        // handle empty list
        if (empty($blockformats)) {
            if (!empty($legacy)) {
                $blockformats = $legacy;
            } else {
                return '';
            }
        }

        $list = array();
        $blocks = array();

        // make an array
        if (is_string($blockformats)) {
            $blockformats = explode(',', $blockformats);
        }

        // create label / value list using default
        foreach ($blockformats as $key) {
            if (array_key_exists($key, self::$formats)) {
                $label = self::$formats[$key];
            }

            // skip html5 blocks for html4 schema
            if ($verify && $schema == 'html4' && in_array($key, $html5)) {
                continue;
            }

            if (isset($label)) {
                $list[$key] = $label;
            }

            $blocks[] = $key;

            // add div container
            if ($key === 'div') {
                $list['div_container'] = 'advanced.div_container';
            }
        }

        // Format list / Remove Format
        $settings['formatselect_blockformats'] = json_encode($list);
    }
}
