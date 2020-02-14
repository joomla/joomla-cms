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

class WFAggregatorExtension_Dailymotion extends WFAggregatorExtension
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
        $document->addScript('dailymotion', 'extensions/aggregator/dailymotion/js');
        $document->addStyleSheet('dailymotion', 'extensions/aggregator/dailymotion/css');
    }

    public function isEnabled()
    {
        $plugin = WFEditorPlugin::getInstance();

        return $plugin->checkAccess('aggregator.dailymotion.enable', 1);
    }

    public function getParams()
    {
        $plugin = WFEditorPlugin::getInstance();

        return array(
            'width' => $plugin->getParam('aggregator.dailymotion.width', 480),
            'height' => $plugin->getParam('aggregator.dailymotion.height', 270),
        );
    }
}
