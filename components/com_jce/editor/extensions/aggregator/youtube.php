<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

class WFAggregatorExtension_Youtube extends WFAggregatorExtension
{
    /**
     * Constructor activating the default information of the class.
     */
    public function __construct()
    {
        parent::__construct(array(
            'format' => 'video',
        ));
    }

    public function display()
    {
        $document = WFDocument::getInstance();
        $document->addScript('youtube', 'extensions/aggregator/youtube/js');
    }

    public function isEnabled()
    {
        $plugin = WFEditorPlugin::getInstance();

        return $plugin->checkAccess('aggregator.youtube.enable', 1);
    }

    public function getParams()
    {
        $plugin = WFEditorPlugin::getInstance();

        return array(
            'width' => $plugin->getParam('aggregator.youtube.width', 560),
            'height' => $plugin->getParam('aggregator.youtube.height', 315),

            'controls' => (int) $plugin->getParam('aggregator.youtube.controls', 1),
            'loop' => (int) $plugin->getParam('aggregator.youtube.loop', 0),
            'autoplay' => (int) $plugin->getParam('aggregator.youtube.autoplay', 0),
            'rel' => (int) $plugin->getParam('aggregator.youtube.related', 1),
            'modestbranding' => (int) $plugin->getParam('aggregator.youtube.modestbranding', 0),
            'privacy' => (int) $plugin->getParam('aggregator.youtube.privacy', 0),
        );
    }
}
