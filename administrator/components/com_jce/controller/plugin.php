<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 3 - http://www.gnu.org/copyleft/gpl.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

require_once JPATH_SITE . '/components/com_jce/editor/libraries/classes/application.php';

class JceControllerPlugin extends JControllerLegacy
{
    private static $map = array(
        'image'     => 'imgmanager',
        'imagepro'  => 'imgmanager_ext'
    );
    
    public function execute($task)
    {
        $wf = WFApplication::getInstance();

        // check a valid profile exists
        $wf->getProfile() or jexit('Invalid Profile');

        // load language files
        $language = JFactory::getLanguage();

        $language->load('com_jce', JPATH_ADMINISTRATOR);

        if (WF_EDITOR_PRO) {
            $language->load('com_jce_pro', JPATH_SITE);
        }

        $plugin = $this->input->get('plugin');

        // get plugin name
        if (strpos($plugin, '.') !== false) {
            list($plugin, $caller) = explode('.', $plugin);
        }

        // map plugin name to internal / legacy name
        if (array_key_exists($plugin, self::$map)) {
            $plugin = self::$map[$plugin];
            $mapped = $plugin;

            if (!empty($caller)) {
                $mapped = $plugin . '.' . $caller;
            }

            $this->input->set('plugin', $mapped);
        }

        $path = WF_EDITOR_PLUGINS . '/' . $plugin;

        if (strpos($plugin, 'editor-') !== false) {
            $path = JPATH_PLUGINS . '/jce/' . $plugin;
        }

        if (!file_exists($path . '/' . $plugin . '.php')) {
            throw new InvalidArgumentException(ucfirst($plugin) . '" not found!');
        }

        include_once $path . '/' . $plugin . '.php';

        $className = 'WF' . ucwords($plugin, '_') . 'Plugin';

        if (class_exists($className)) {
            $instance = new $className();

            if (strpos($task, '.') !== false) {
                list($name, $task) = explode('.', $task);
            }

            if ($task === 'display') {
                $task = 'execute';
            }
    
            // default to execute if task is not available
            if (is_callable(array($instance, $task)) === false) {
                $task = 'execute';
            }
    
            $instance->$task();
        }

        jexit();
    }
}