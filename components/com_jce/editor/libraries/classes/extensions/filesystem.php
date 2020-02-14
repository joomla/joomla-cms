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

class WFFileSystem extends WFExtension
{
    /**
     * Constructor activating the default information of the class.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->setProperties(array_merge($config, array(
            'local' => true,
            'upload' => array(
                'stream' => false,
                'chunking' => false,
                'unique_filenames' => false,
            ),
        )));
    }

    /**
     * Returns a reference to a plugin object.
     *
     * This method must be invoked as:
     *    <pre>  $advlink =AdvLink::getInstance();</pre>
     *
     * @return JCE The editor object
     *
     * @since 1.5
     */
    public static function getInstance($type = 'joomla', $config = array())
    {
        static $instance;

        if (!is_object($instance)) {
            $fs = parent::loadExtensions('filesystem', $type);

            // load the default...
            if (empty($fs)) {
                $fs = parent::loadExtensions('filesystem', 'joomla');
            }

            // get the first filesystem extension only
            if (is_array($fs)) {
                $fs = array_shift($fs);
            }

            $classname = 'WF' . ucfirst($fs->name) . 'FileSystem';

            if (class_exists($classname)) {
                $instance = new $classname($config);
            } else {
                $instance = new self($config);
            }
        }

        return $instance;
    }

    /**
     * Get the base directory.
     *
     * @return string base dir
     */
    public function getBaseDir()
    {
        return WFUtility::makePath(JPATH_SITE, $this->getRootDir());
    }

    /**
     * Get the full base url.
     *
     * @return string base url
     */
    public function getBaseURL()
    {
        return WFUtility::makePath(JURI::root(true), $this->getRootDir());
    }

    /**
     * Return the full user directory path. Create if required.
     *
     * @param string  The base path
     *
     * @return Full path to folder
     */
    public function getRootDir()
    {
        static $root;

        if (!isset($root)) {
            $user = JFactory::getUser();
            $wf = WFApplication::getInstance();
            $profile = $wf->getProfile();

            // Get base directory as shared parameter
            $root = $this->get('dir', '');

            // Remove whitespace
            $root = trim($root);

            if (!empty($root)) {
                // Convert slashes / Strip double slashes
                $root = preg_replace('/[\\\\]+/', '/', $root);
                // Remove first leading slash
                $root = ltrim($root, '/');
                // Force default directory if base param starts with a variable or a . eg $id
                if (preg_match('/[\.\$]/', $root{0})) {
                    $root = 'images';
                }

                jimport('joomla.user.helper');

                $groups = JUserHelper::getUserGroups($user->id);

                // get keys only
                $groups = array_keys($groups);

                // get the first group
                $group_id = array_shift($groups);

                if (is_int($group_id)) {
                    // usergroup table
                    $group = JTable::getInstance('Usergroup');
                    $group->load($group_id);
                    // usertype
                    $usertype = $group->title;
                } else {
                    $usertype = $group_id;
                }

                // Replace any path variables
                $pattern = array('/\$id/', '/\$username/', '/\$user(group|type)/', '/\$(group|profile)/', '/\$day/', '/\$month/', '/\$year/');
                $replace = array($user->id, $user->username, $usertype, $profile->name, date('d'), date('m'), date('Y'));
                $root = preg_replace($pattern, $replace, $root);

                // split into path parts to preserve /
                $parts = explode('/', $root);

                $textcase = $wf->getParam('editor.websafe_textcase', '');

                // implode textcase array to create string
                if (is_array($textcase)) {
                    $textcase = implode(',', $textcase);
                }

                // clean path parts
                $parts = WFUtility::makeSafe($parts, $wf->getParam('editor.websafe_mode', 'utf-8'), $wf->getParam('editor.websafe_allow_spaces', '_'), $textcase);

                //join path parts
                $root = implode('/', $parts);
            }
        }

        return $root;
    }

    protected static function sortItemsByKey($items, $type)
    {
        $sortable = array();

        // set default direction
        $direction = 'asc';

        if ($type[0] === '-') {
            $direction = 'desc';
            $type = substr($type, 1);
        }

        foreach ($items as $key => $item) {
            $sortable[$key] = isset($item[$type]) ? $item[$type] : $item['properties'][$type];
        }

        array_multisort($sortable, $direction === 'desc' ? SORT_DESC : SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE, $items);

        return $items;
    }

    public function toAbsolute($path)
    {
        return $path;
    }

    public function toRelative($path)
    {
        return $path;
    }

    public function getTotalSize($path, $recurse = true)
    {
        return 0;
    }

    public function countFiles($path, $recurse = false)
    {
        return 0;
    }

    public function getFiles($path, $filter)
    {
        return array();
    }

    public function getFolders($path, $filter)
    {
        return array();
    }

    public function getSourceDir($path)
    {
        return $path;
    }

    public function isMatch($needle, $haystack)
    {
        return $needle == $haystack;
    }

    public function pathinfo($path)
    {
        return pathinfo($path);
    }

    public function delete($path)
    {
        return true;
    }

    public function createFolder($path, $new)
    {
        return true;
    }

    public function rename($src, $dest)
    {
        return true;
    }

    public function copy($src, $dest)
    {
        return true;
    }

    public function move($src, $dest)
    {
        return true;
    }

    public function getFolderDetails($path)
    {
        return array(
            'properties' => array('modified' => ''),
        );
    }

    public function getFileDetails($path)
    {
        $data = array(
            'properties' => array(
                'size' => '',
                'modified' => '',
            ),
        );

        if (preg_match('#\.(jpg|jpeg|bmp|gif|tiff|png)#i', $path)) {
            $image = array(
                'properties' => array(
                    'width' => 0,
                    'height' => 0,
                    'preview' => '',
                ),
            );

            return array_merge_recursive($data, $image);
        }

        return $data;
    }

    public function getDimensions($path)
    {
        return array(
            'width' => '',
            'height' => '',
        );
    }

    public function upload($method, $src, $dir, $name, $chunks = 0, $chunk = 0)
    {
        return true;
    }

    public function exists($path)
    {
        return true;
    }

    public function read($path)
    {
        return '';
    }

    public function write($path, $content)
    {
        return true;
    }

    public function isLocal()
    {
        return $this->get('local') === true;
    }

    public function is_file($path)
    {
        return true;
    }

    public function is_dir($path)
    {
        return true;
    }
}

/**
 * Filesystem Error class.
 */
final class WFFileSystemResult
{
    /*
     * @var Object type eg: file / folder
     */

    public $type = 'files';
    /*
     * @boolean    Result state
     */
    public $state = false;
    /*
     * @int    Error code
     */
    public $code = null;
    /*
     * @var Error message
     */
    public $message = null;
    /*
     * @var File / Folder path
     */
    public $path = null;
    /*
     * @var File / Folder url
     */
    public $url = null;

    public function __construct()
    {
    }
}
