<?php
/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved
 * @copyright   Copyright (C) 2018 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * JCE extension plugin.
 *
 * @since  2.6
 */
class PlgExtensionJce extends JPlugin
{
    /**
     * Check the installer is for a valid plugin group.
     *
     * @param JInstaller $installer Installer object
     *
     * @return bool
     *
     * @since   2.6
     */
    private function isValidPlugin($installer)
    {
        if (empty($installer->manifest)) {
            return false;
        }

        foreach (array('type', 'group') as $var) {
            $$var = (string) $installer->manifest->attributes()->{$var};
        }

        return $type === 'plugin' && $group === 'jce';
    }

    public function onExtensionBeforeInstall($method, $type, $manifest, $extension = 0)
    {
        if ((string) $type === "file") {

            // get a reference to the current installer
            $manifestPath = JInstaller::getInstance()->getPath('manifest');

            if (empty($manifestPath)) {
                return true;
            }

            // get the filename of the manifest file, eg: pkg_jce_de-DE
            $element = basename($manifestPath, '.xml');

            // if this matches the current install...
            if (strpos($element, 'pkg_jce_') !== false) {
                // find an existing legacy language install, eg: jce-de-DE
                $element = str_replace('pkg_jce_', 'jce-', $element);

                $table = JTable::getInstance('extension');
                $id = $table->find(array('type' => 'file', 'element' => $element));

                if ($id) {
                    $installer = new JInstaller();

                    // try unisntall, if this fails, delete database entry
                    if (!$installer->uninstall('file', $id)) {
                        $table->delete($id);
                    }
                }
            }
        }
    }
    /**
     * Handle post extension install update sites.
     *
     * @param JInstaller $installer Installer object
     * @param int        $eid       Extension Identifier
     *
     * @since   2.6
     */
    public function onExtensionAfterInstall($installer, $eid)
    {
        if ($eid) {
            if (!$this->isValidPlugin($installer)) {
                return false;
            }

            $basename = basename($installer->getPath('extension_root'));

            if (strpos($basename, '-') === false) {
                return false;
            }

            require_once JPATH_ADMINISTRATOR . '/components/com_jce/helpers/plugins.php';

            // enable plugin
            $plugin = JTable::getInstance('extension');
            $plugin->load($eid);
            $plugin->publish();

            $parts = explode('-', $basename);
            $type = $parts[0];
            $name = $parts[1];

            $plugin = new StdClass();
            $plugin->name = $name;

            if ($type === 'editor') {
                $plugin->icon = (string) $installer->manifest->icon;
                $plugin->row = (int) (string) $installer->manifest->attributes()->row;
                $plugin->type = 'plugin';
            } else {
                $plugin->type = 'extension';
            }

            $plugin->path = $installer->getPath('extension_root');

            JcePluginsHelper::postInstall('install', $plugin, $installer);

            // clean up legacy extensions
            if ($plugin->type == 'extension') {
                jimport('joomla.filesystem.folder');
                jimport('joomla.filesystem.file');

                $path = JPATH_SITE . '/components/com_jce/editor/extensions/' . $type;

                // delete manifest
                if (is_file($path . '/' . $plugin->name . '.xml')) {
                    JFile::delete($path . '/' . $plugin->name . '.xml');
                }
                // delete file
                if (is_file($path . '/' . $plugin->name . '.php')) {
                    JFile::delete($path . '/' . $plugin->name . '.php');
                }
                // delete folder
                if (is_dir($path . '/' . $plugin->name)) {
                    JFolder::delete($path . '/' . $plugin->name);
                }
            }
        }
    }

    /**
     * Handle extension uninstall.
     *
     * @param JInstaller $installer Installer instance
     * @param int        $eid       Extension id
     * @param int        $result    Installation result
     *
     * @since   1.6
     */
    public function onExtensionAfterUninstall($installer, $eid, $result)
    {
        if ($eid) {
            if (!$this->isValidPlugin($installer)) {
                return false;
            }

            $basename = basename($installer->getPath('extension_root'));

            if (strpos($basename, '-') === false) {
                return false;
            }

            require_once JPATH_ADMINISTRATOR . '/components/com_jce/helpers/plugins.php';

            $parts = explode('-', $basename);
            $type = $parts[0];
            $name = $parts[1];

            $plugin = new StdClass();
            $plugin->name = $name;

            if ($type === 'editor') {
                $plugin->icon = (string) $installer->manifest->icon;
                $plugin->row = (int) (string) $installer->manifest->attributes()->row;
                $plugin->type = 'plugin';
            }

            $plugin->path = $installer->getPath('extension_root');

            JcePluginsHelper::postInstall('uninstall', $plugin, $installer);
        }
    }
}
