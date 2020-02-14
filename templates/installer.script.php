<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

class plgSystemTmp_helixultInstallerScript
{

    public function postflight($type, $parent)
    {
        $src = $parent->getParent()->getPath('source');
        $manifest = $parent->getParent()->manifest;
        $plugins = $manifest->xpath('plugins/plugin');

        foreach ($plugins as $key => $plugin)
        {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $installer = new JInstaller;

            $path = $src.'/plugins/'.$group;
            if (JFolder::exists($src.'/plugins/'.$group.'/'.$name))
            {
                $path = $src.'/plugins/'.$group.'/'.$name;
            }

            $plugin_info = $this->getPluginInfoByName($name, $group);
            if($plugin_info)
            {
                $manifest_cache = json_decode($plugin_info->manifest_cache);
                $cache_version = $manifest_cache->version;

                $plg_manifest = $installer->parseXMLInstallFile($path.'/'.$name.'.xml');
                $version = $plg_manifest['version'];

                if($version < $cache_version)
                {
                    continue;
                }
            }

            $result = $installer->install($path);
            if ($result)
            {
                $this->activeInstalledPlugin($name, $group);
            }
        }

        $template_path = $src.'/template';
        if (JFolder::exists( $template_path ))
        {
            $installer = new JInstaller;
            $result = $installer->install($template_path);
        }
        $templates = $manifest->xpath('template');

        foreach($templates as $key => $template)
        {
            $tmpl_name = (string)$template->attributes()->name;
            $tmpl_info = $this->getTemplateInfoByName($tmpl_name);

            $params = json_decode($tmpl_info->params);
            $params_array = (array)$params;

            if(empty($params_array))
            {
                $options_default = file_get_contents($template_path .'/options.json');

                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $fields = array(
                    $db->quoteName('params') . ' = ' . $db->quote($options_default)
                );

                $conditions = array(
                    $db->quoteName('client_id') . ' = 0',
                    $db->quoteName('template') . ' = ' . $db->quote($tmpl_name)
                );

                $query->update($db->quoteName('#__template_styles'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $db->execute();
            }
        }

        $conf = JFactory::getConfig();
        $conf->set('debug', false);
        $parent->getParent()->abort();
    }

    private function getTemplateInfoByName($name)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__template_styles'));
        $query->where($db->quoteName('client_id') . ' = 0');
        $query->where($db->quoteName('template') . ' = ' . $db->quote( $name ));

        $db->setQuery($query);

        return $db->loadObject();
    }

    private function activeInstalledPlugin($name, $group)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('enabled') . ' = 1'
        );

        $conditions = array(
            $db->quoteName('type') . ' = ' . $db->quote('plugin'), 
            $db->quoteName('element') . ' = ' . $db->quote($name),
            $db->quoteName('folder') . ' = ' . $db->quote($group)
        );

        $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }

    private function getPluginInfoByName($name, $group)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__extensions'));
        $query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
        $query->where($db->quoteName('element') . ' = ' . $db->quote( $name ));
        $query->where($db->quoteName('folder') . ' = ' . $db->quote( $group ));

        $db->setQuery($query);

        return $db->loadObject();
    }


    public function abort($msg = null, $type = null){
        if ($msg) {
            JError::raiseWarning(100, $msg);
        }
        foreach ($this->packages as $package) {
            $package['installer']->abort(null, $type);
        }
    }
}
