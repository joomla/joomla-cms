<?php
/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 3 - http://www.gnu.org/copyleft/gpl.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

// define admin base path
define('WF_ADMIN', __DIR__);

$app = JFactory::getApplication();

// throw exception for legacy task
if ($app->input->get('task') === 'plugin') {
    throw new Exception('Restricted', 403);
}

// fix legacy plugin url
if ($app->input->get('view') === 'editor' && $app->input->get('layout') === 'plugin') {

    if ($app->input->get('plugin')) {
        $app->input->set('task', 'plugin.display');
    }    
}

// constants and autoload 
require_once __DIR__ . '/includes/base.php';

$controller = JControllerLegacy::getInstance('Jce', array('base_path' => __DIR__));

$controller->execute($app->input->get('task'));
$controller->redirect();
