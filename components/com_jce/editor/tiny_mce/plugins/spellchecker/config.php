<?php

/**
 * @copyright    Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license    GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFSpellcheckerPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();
        $engine = $wf->getParam('spellchecker.engine', 'browser', 'browser');

        switch ($engine) {
            default:
            case 'browser':
            case 'googlespell':
                $languages = '';

                $settings['spellchecker_browser_state'] = $wf->getParam('spellchecker.browser_state', 0, 0);

                $engine = 'browser';

                break;

            case 'pspell':
            case 'pspellshell':
                $languages = (array) $wf->getParam('spellchecker.languages', 'English=en', '');

                if ($engine === 'pspellshell') {
                    $engine = 'pspell';
                }

                if (!function_exists('pspell_new')) {
                    $engine = 'browser';
                }

                break;
            case 'enchantspell':
                $languages = (array) $wf->getParam('spellchecker.languages', 'English=en', '');

                if (!function_exists('enchant_broker_init')) {
                    $engine = 'browser';
                }
                break;
        }

        if (!empty($languages)) {
            $settings['spellchecker_languages'] = '+'.implode(',', $languages);
        }

        // only needs to be set if not "browser"
        if ($engine !== "browser") {
            $settings['spellchecker_engine'] = $engine;

            $settings['spellchecker_suggestions'] = $wf->getParam('spellchecker.suggestions', 1, 1);
        }
    }
}
