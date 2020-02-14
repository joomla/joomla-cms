<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFArticlePluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        //$settings['article_hide_xtd_btns']     = $wf->getParam('article.hide_xtd_btns', 0, 0);
        $settings['article_show_readmore'] = $wf->getParam('article.show_readmore', 1, 1);
        $settings['article_show_pagebreak'] = $wf->getParam('article.show_pagebreak', 1, 1);
    }
}
