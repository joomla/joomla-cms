<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFMediaPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $tags = array();

        if ($wf->getParam('media.iframes', 0)) {
            $tags[] = 'iframe';
        }

        if ($wf->getParam('media.audio', 1)) {
            $tags[] = 'audio';
        }

        if ($wf->getParam('media.video', 1)) {
            $tags[] = 'video';
        }

        if (in_array('audio', $tags) || in_array('video', $tags)) {
            $tags[] = 'source';
        }

        if ($wf->getParam('media.embed', 1)) {
            $tags[] = 'embed';
        }

        if ($wf->getParam('media.object', 1)) {
            $tags[] = 'object';
            $tags[] = 'param';
        }

        $settings['invalid_elements'] = array_diff($settings['invalid_elements'], $tags);
        $settings['media_live_embed'] = $wf->getParam('media.live_embed', 0, 0);
    }
}
