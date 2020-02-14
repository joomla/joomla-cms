<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

abstract class JceProfilesHelper
{

    /**
     * Create the Profiles table.
     *
     * @return bool
     */
    public static function createProfilesTable()
    {
        jimport('joomla.installer.helper');

        $app = JFactory::getApplication();

        $db = JFactory::getDBO();
        $driver = strtolower($db->name);

        switch ($driver) {
            default:
            case 'mysql':
            case 'mysqli':
                $driver = 'mysql';
                break;
            case 'sqlsrv':
            case 'sqlazure':
            case 'sqlzure':
                $driver = 'sqlsrv';
                break;
            case 'postgresql':
            case 'pgsql':
                $driver = 'postgresql';
                break;
        }

        $file = JPATH_ADMINISTRATOR . '/components/com_jce/sql/' . $driver . '.sql';
        $error = null;

        if (is_file($file)) {
            $query = file_get_contents($file);

            if ($query) {
                // replace prefix
                $query = $db->replacePrefix((string) $query);

                // set query
                $db->setQuery(trim($query));

                if (!$db->execute()) {
                    $app->enqueueMessage(JText::_('WF_INSTALL_TABLE_PROFILES_ERROR') . $db->stdErr(), 'error');

                    return false;
                } else {
                    return true;
                }
            } else {
                $error = 'NO SQL QUERY';
            }
        } else {
            $error = 'SQL FILE MISSING';
        }

        $app->enqueueMessage(JText::_('WF_INSTALL_TABLE_PROFILES_ERROR') . !is_null($error) ? ' - ' . $error : '', 'error');

        return false;
    }

    /**
     * Install Profiles.
     *
     * @return bool
     *
     * @param object $install[optional]
     */
    public static function installProfiles()
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();

        if (self::createProfilesTable()) {
            self::buildCountQuery();

            $profiles = array('Default' => false, 'Front End' => false);

            // No Profiles table data
            if (!$db->loadResult()) {
                $xml = JPATH_ADMINISTRATOR . '/components/com_jce/models/profiles.xml';

                if (is_file($xml)) {
                    if (!self::processImport($xml)) {
                        $app->enqueueMessage(JText::_('WF_INSTALL_PROFILES_ERROR'), 'error');

                        return false;
                    }
                } else {
                    $app->enqueueMessage(JText::_('WF_INSTALL_PROFILES_NOFILE_ERROR'), 'error');

                    return false;
                }
            }

            return true;
        }

        return false;
    }

    private static function buildCountQuery($name = '')
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);

        // check for name
        $query->select('COUNT(id)')->from('#__wf_profiles');

        if ($name) {
            $query->where('name = ' . $db->Quote($name));
        }

        $db->setQuery($query);
    }

    public static function getDefaultProfile()
    {
        $mainframe = JFactory::getApplication();
        $file = JPATH_ADMINISTRATOR . '/components/com_jce/models/profiles.xml';

        $xml = simplexml_load_file($file);

        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jce/tables');

        if ($xml) {
            foreach ($xml->profiles->children() as $profile) {
                if ($profile->attributes()->default) {
                    $table = JTable::getInstance('Profiles', 'JceTable');

                    foreach ($profile->children() as $item) {
                        switch ($item->getName()) {
                            case 'rows':
                                $table->rows = (string) $item;
                                break;
                            case 'plugins':
                                $table->plugins = (string) $item;
                                break;
                            default:
                                $key = $item->getName();
                                $table->$key = (string) $item;

                                break;
                        }
                    }

                    // reset name and description
                    $table->name = '';
                    $table->description = '';

                    return $table;
                }
            }
        }

        return null;
    }

    /**
     * Check whether a table exists.
     *
     * @return bool
     *
     * @param string $table Table name
     */
    public static function checkTable()
    {
        $db = JFactory::getDBO();

        $tables = $db->getTableList();

        if (!empty($tables)) {
            // swap array values with keys, convert to lowercase and return array keys as values
            $tables = array_keys(array_change_key_case(array_flip($tables)));
            $app = JFactory::getApplication();
            $match = str_replace('#__', strtolower($app->getCfg('dbprefix', '')), '#__wf_profiles');

            return in_array($match, $tables);
        }

        // try with query
        self::buildCountQuery();

        return $db->execute();
    }

    /**
     * Check table contents.
     *
     * @return int
     *
     * @param string $table Table name
     */
    public static function checkTableContents()
    {
        $db = JFactory::getDBO();

        self::buildCountQuery();

        return $db->loadResult();
    }

    public static function getUserGroups($area)
    {
        $db = JFactory::getDBO();

        jimport('joomla.access.access');

        $query = $db->getQuery(true);

        $query->select('id')->from('#__usergroups');

        $db->setQuery($query);
        $groups = $db->loadColumn();

        $front = array();
        $back = array();

        foreach ($groups as $group) {
            $create = JAccess::checkGroup($group, 'core.create');
            $admin = JAccess::checkGroup($group, 'core.login.admin');
            $super = JAccess::checkGroup($group, 'core.admin');

            if ($super) {
                $back[] = $group;
            } else {
                // group can create
                if ($create) {
                    // group has admin access
                    if ($admin) {
                        $back[] = $group;
                    } else {
                        $front[] = $group;
                    }
                }
            }
        }

        switch ($area) {
            case 0:
                return array_merge($front, $back);
                break;
            case 1:
                return $front;
                break;
            case 2:
                return $back;
                break;
        }

        return array();
    }

    /**
     * Process import data from XML file.
     *
     * @param object $file    XML file
     * @param bool   $install Can be used by the package installer
     */
    public static function processImport($file)
    {
        $n = 0;

        $app = JFactory::getApplication();
        $xml = simplexml_load_file($file);

        $user = JFactory::getUser();
        $date = new JDate();

        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jce/tables');

        $language = JFactory::getLanguage();
        $language->load('com_jce', JPATH_ADMINISTRATOR, null, true);

        if ($xml) {
            foreach ($xml->profiles->children() as $profile) {
                $table = JTable::getInstance('Profiles', 'JceTable');

                foreach ($profile->children() as $item) {
                    $key = $item->getName();
                    $value = (string) $item;

                    switch ($key) {
                        case 'name':
                            // only if name set and table name not set
                            if ($value) {
                                // create name copy if exists
                                while ($table->load(array('name' => $value))) {
                                    if ($value === $table->name) {
                                        $value = Joomla\String\StringHelper::increment($value);
                                    }
                                }
                            }
                            break;

                        case 'description':
                            $value = JText::_($value);
                            break;
                        case 'types':

                            if ($value === "") {
                                $area = (string) $profile->area[0] || 0;
                                $groups = self::getUserGroups($area);
                                $value = implode(',', array_unique($groups));
                            }
                            break;
                        case 'users':
                            break;
                        case 'area':
                            if ($value === "") {
                                $value = '0';
                            }

                            break;
                        case 'components':
                            break;
                        case 'params':
                            if (empty($value)) {
                                $value = "{}";
                            }
                            break;
                        case 'rows':
                            break;
                        case 'plugins':
                            break;
                        case 'area':
                        case 'published':
                        case 'ordering':
                            $value = (int) $value;
                            break;
                    }

                    $table->$key = $value;
                }

                // set new id
                $table->id = 0;

                // set checked_out
                $table->checked_out = $user->get('id');

                // set checked_out_time
                $table->checked_out_time = $date->toSQL();

                if (!$table->store()) {
                    $app->enqueueMessage($table->getError(), 'error');
                    return false;
                }

                // check-in
                $table->checkin();

                ++$n;
            }
        }

        return $n;
    }

    /**
     * CDATA encode a parameter if it contains & < > characters, eg: <![CDATA[index.php?option=com_content&view=article&id=1]]>.
     *
     * @param object $param
     *
     * @return CDATA encoded parameter or parameter
     */
    public static function encodeData($data)
    {
        if (preg_match('/[<>&]/', $data)) {
            $data = '<![CDATA[' . $data . ']]>';
        }

        $data = preg_replace('/"/', '\"', $data);

        return $data;
    }
}
