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

class WFFileBrowser extends JObject
{
    /* @var array */
    private $_buttons = array();

    /* @var array */
    private $_actions = array();

    /* @var array */
    private $_events = array();

    /* @var array */
    private $_result = array('error' => array(), 'files' => array(), 'folders' => array());

    /* @var string */
    public $dir = '';

    /* @var string */
    public $filesystem = 'joomla';

    /* @var string */
    public $filetypes = 'jpg,jpeg,png,gif,webp';

    /* @var array */
    public $upload = array(
        'max_size' => 1024,
        'validate_mimetype' => 1,
        'add_random' => 0,
        'total_files' => 0,
        'total_size' => 0,
        'remove_exif' => 0,
    );
    /* @var int */
    public $folder_tree = 1;

    /* @var string */
    public $list_limit = 'all';

    /* @var array */
    public $features = array(
        'upload' => 1,
        'folder' => array(
            'create' => 1,
            'delete' => 1,
            'rename' => 1,
            'move' => 1,
        ),
        'file' => array(
            'rename' => 1,
            'delete' => 1,
            'move' => 1,
        ),
    );
    /* @var string */
    public $date_format = '%d/%m/%Y, %H:%M';

    /* @var string */
    public $websafe_mode = 'utf-8';

    /* @var int */
    public $websafe_spaces = 0;

    /* @var string */
    public $websafe_textcase = '';

    public function __construct($config = array())
    {
        // set file browser config
        $this->setConfig($config);

        // add actions
        $this->addDefaultActions();
        // add buttons
        $this->addDefaultButtons();

        // Setup XHR callback funtions
        $this->setRequest(array($this, 'getItems'));
        $this->setRequest(array($this, 'getFileDetails'));
        $this->setRequest(array($this, 'getFolderDetails'));
        $this->setRequest(array($this, 'getTree'));
        $this->setRequest(array($this, 'getTreeItem'));

        $this->setRequest(array($this, 'upload'));
    }

    /**
     * Display the browser.
     */
    public function display()
    {
        $this->setProperties(array(
            'actions' => $this->getActions(),
            'buttons' => $this->getButtons(),
        ));

        // Get the Document instance
        $document = WFDocument::getInstance();

        $document->addScript(array('filebrowser.min'), 'libraries');
        $document->addStyleSheet(array('filebrowser.min'), 'libraries');
    }

    /**
     * Render the browser view.
     */
    public function render()
    {
        $session = JFactory::getSession();

        $view = new WFView(array(
            'name' => 'filebrowser',
            'layout' => 'default',
        ));

        // assign session data
        $view->assign('session', $session);
        // assign form action
        $view->assign('action', $this->getFormAction());
        // return view output
        $view->display();
    }

    /**
     * Set a WFRequest item.
     *
     * @param array $request
     */
    public function setRequest($request)
    {
        $xhr = WFRequest::getInstance();
        $xhr->setRequest($request);
    }

    /**
     * Upload form action url.
     *
     * @return string URL
     *
     * @since    1.5
     */
    protected function getFormAction()
    {
        $wf = WFEditorPlugin::getInstance();

        $context = JFactory::getApplication()->input->getInt('context');

        $query = '';

        $args = array(
            'plugin' => $wf->getName(),
            'context' => $context,
        );

        foreach ($args as $k => $v) {
            $query .= '&' . $k . '=' . $v;
        }

        return JURI::base(true) . '/index.php?option=com_jce&task=plugin.rpc' . $query;
    }

    public function getFileSystem()
    {
        static $filesystem;

        if (!is_object($filesystem)) {
            $wf = WFEditorPlugin::getInstance();

            $config = array(
                'dir' => $this->get('dir'),
                'upload_conflict' => $wf->getParam('editor.upload_conflict', 'overwrite'),
                'upload_suffix' => $wf->getParam('editor.upload_suffix', '_copy'),
                'filetypes' => $this->listFileTypes(),
            );

            $filesystem = WFFileSystem::getInstance($this->get('filesystem'), $config);
        }

        return $filesystem;
    }

    private function getViewable()
    {
        return 'jpeg,jpg,gif,png,webp,avi,wmv,wm,asf,asx,wmx,wvx,mov,qt,mpg,mp3,mp4,m4v,mpeg,ogg,ogv,webm,swf,flv,f4v,xml,dcr,rm,ra,ram,divx,html,htm,txt,rtf,pdf,doc,docx,xls,xlsx,ppt,pptx';
    }

    /**
     * Return a list of allowed file extensions in specific format.
     *
     * @return mixed formatted extension list
     */
    public function getFileTypes($format = 'map', $list = '')
    {
        if (empty($list)) {
            $list = $this->get('filetypes');
        }

        $data = array();

        foreach (explode(';', $list) as $group) {
            // exclude group
            if (strpos($group, '=') !== false && strpos($group, '-') === 0) {
                continue;
            }

            $parts = explode('=', $group);
            // get extensions, eg: "jpg,gif,png"
            $items = array_pop($parts);
            // get type if available, eg: "images"
            $type = array_pop($parts);

            // re-map without excluded items
            $items = array_filter(explode(',', $items), function ($item) {
                return substr(trim($item), 0, 1) !== '-';
            });

            // no type
            if (empty($type)) {
                $data = $items;
            } else {
                // create flattened array, eg: ["jpg", "jpeg", "gif", "png"]
                if ($format === 'array' || $format === 'list') {
                    $data = array_merge($data, array_map('strtolower', $items));
                    // create associative array, eg:  or ["images" => ["jpg", "jpeg", "gif", "png"]]
                } else {
                    $data[$type] = $items;
                }
            }
        }

        // return flattended list of extensions, eg: "jpg,jpeg,png,gif"
        if ($format === 'list') {
            return implode(',', $data);
        }

        // return json encoded list, eg: {"images": ["jpg", "jpeg", "gif", "png"]}
        if ($format === 'json') {
            return json_encode($data);
        }
        // return array
        return $data;
    }

    /**
     * Converts the extensions map to a list.
     *
     * @param string $list The extensions map eg: images=jpg,jpeg,gif,png
     *
     * @return string jpg,jpeg,gif,png
     */
    private function listFileTypes($list = '')
    {
        return $this->getFileTypes('list', $list);
    }

    public function setFileTypes($list = 'jpg,jpeg,png,gif')
    {
        if ($list && $list[0] === '=') {
            $list = substr($list, 1);
        }

        $this->set('filetypes', $list);
    }

    public function addFileTypes($filetypes)
    {
        $list = $this->get('filetypes');

        if (strpos($list, '=') === false) {
            // convert to array if needed
            if (is_string($list)) {
                $list = explode(',', $list);
            }
            // combine
            $list = array_unique(array_merge($list, $filetypes));
            // convert to string
            $list = implode(',', $list);
        } else {
            $list = explode(';', $list);

            foreach ($filetypes as $group => $extensions) {
                $list[] = $group . '=' . $extensions;
            }

            $list = implode(';', $list);
        }

        $this->set('filetypes', $list);
    }

    /**
     * Returns the result variable.
     *
     * @return var $_result
     */
    public function getResult()
    {
        return $this->_result;
    }

    public function setResult($value, $key = null)
    {
        if ($key) {
            $this->_result[$key][] = $value;
        } else {
            $this->_result = $value;
        }
    }

    public function checkFeature($action, $type = null)
    {
        $features = $this->get('features');

        if ($type) {
            if (isset($features[$type])) {
                $type = $features[$type];

                if (isset($type[$action])) {
                    return (bool) $type[$action];
                }
            }
        } else {
            if (isset($features[$action])) {
                return (bool) $features[$action];
            }
        }

        return false;
    }

    protected function checkPathAccess($path)
    {
        $filter = $this->get('filter');

        if (!empty($filter)) {
            $path = ltrim($path, '/');

            return !in_array($path, (array) $filter);
        }

        return true;
    }

    public function getBaseDir()
    {
        $filesystem = $this->getFileSystem();

        return $filesystem->getBaseDir();
    }

    /**
     * Get the list of files in a given folder.
     *
     * @param string $relative The relative path of the folder
     * @param string $filter   A regex filter option
     *
     * @return array list array
     */
    private function getFiles($relative, $filter = '.', $sort = '')
    {
        $filesystem = $this->getFileSystem();
        $list = $filesystem->getFiles($relative, $filter, $sort);

        return $list;
    }

    /**
     * Get the list of folder in a given folder.
     *
     * @param string $relative The relative path of the folder
     *
     * @return array list array
     */
    private function getFolders($relative, $filter = '', $sort = '')
    {
        $filesystem = $this->getFileSystem();
        $list = $filesystem->getFolders($relative, $filter, $sort);

        $filters = $this->get('filter');

        // remove filtered items
        if (!empty($filters)) {
            $list = array_filter($list, function ($item) use ($filters) {
                // remove leading slash
                $id = ltrim($item['id'], '/');

                return !in_array($id, $filters);

                /*foreach($filters as $filter) {
            // show this folder
            if ($filter{0} === "+") {
            return substr($filter, 1) === $id;
            }
            // hide this folder
            if ($filter{0} === "-") {
            $filter = substr($filter, 1);
            }

            return $filter !== $id;
            }*/
            });
        }

        return $list;
    }

    /**
     * Get file and folder lists.
     *
     * @return array Array of file and folder list objects
     *
     * @param string $relative Relative or absolute path based either on source url or current directory
     * @param int    $limit    List limit
     * @param int    $start    list start point
     */
    public function getItems($path, $limit = 25, $start = 0, $filter = '', $sort = '')
    {
        $filesystem = $this->getFileSystem();

        $files = array();
        $folders = array();

        clearstatcache();

        // decode path
        $path = rawurldecode($path);

        WFUtility::checkPath($path);

        // trim leading slash
        $path = ltrim($path, '/');

        // get source dir from path eg: images/stories/fruit.jpg = images/stories
        $dir = $filesystem->getSourceDir($path);

        $filetypes = (array) $this->getFileTypes('array');

        $name = '';

        if ($filter) {
            if ($filter{0} == '.') {
                $ext = WFUtility::makeSafe($filter);

                for ($i = 0; $i < count($filetypes); ++$i) {
                    if (preg_match('#^' . $ext . '#', $filetypes[$i]) === false) {
                        unset($filetypes[$i]);
                    }
                }
            } else {
                $name = '^(?i).*' . WFUtility::makeSafe($filter) . '.*';
            }
        }

        // get file list by filter
        $files = $this->getFiles($dir, $name . '\.(?i)(' . implode('|', $filetypes) . ')$', $sort);

        if (empty($filter) || $filter{0} != '.') {
            // get folder list
            $folders = $this->getFolders($dir, '^(?i).*' . WFUtility::makeSafe($filter) . '.*', $sort);
        }

        $folderArray = array();
        $fileArray = array();

        $items = array_merge($folders, $files);

        if ($items) {
            if (is_numeric($limit)) {
                $items = array_slice($items, $start, $limit);
            }

            foreach ($items as $item) {
                $item['classes'] = '';
                if ($item['type'] == 'folders') {
                    $folderArray[] = $item;
                } else {
                    // check for selected item
                    $item['selected'] = $filesystem->isMatch($item['url'], $path);
                    $fileArray[] = $item;
                }
            }
        }

        $result = array(
            'folders' => $folderArray,
            'files' => $fileArray,
            'total' => array(
                'folders' => count($folders),
                'files' => count($files),
            ),
        );

        // Fire Event passing result as reference
        $this->fireEvent('onGetItems', array(&$result));

        return $result;
    }

    /**
     * Get a tree node.
     *
     * @param string $dir The relative path of the folder to search
     *
     * @return Tree node array
     */
    public function getTreeItem($path)
    {
        $filesystem = $this->getFileSystem();
        $path = rawurldecode($path);

        WFUtility::checkPath($path);

        // get source dir from path eg: images/stories/fruit.jpg = images/stories
        $dir = $filesystem->getSourceDir($path);

        $folders = $this->getFolders($dir);
        $array = array();
        if (!empty($folders)) {
            foreach ($folders as $folder) {
                $array[] = array(
                    'id' => $folder['id'],
                    'name' => $folder['name'],
                    'class' => 'folder',
                );
            }
        }
        $result = array(
            'folders' => $array,
        );

        return $result;
    }

    /**
     * Escape a string.
     *
     * @return string Escaped string
     *
     * @param string $string
     */
    private function escape($string)
    {
        $revert = array('%2A' => '*', '%2B' => '+', '%2F' => '/', '%3F' => '?', '%40' => '@');

        return strtr(rawurlencode($string), $revert);
    }

    /**
     * Build a tree list.
     *
     * @param string $dir The relative path of the folder to search
     *
     * @return Tree html string
     */
    public function getTree($path = '')
    {
        $filesystem = $this->getFileSystem();

        // decode path
        $path = rawurldecode($path);

        WFUtility::checkPath($path);

        // get source dir from path eg: images/stories/fruit.jpg = /stories
        $dir = $filesystem->getSourceDir($path);

        // remove leading slash
        $dir = ltrim($dir, '/');

        $result = $this->getTreeItems($dir);

        return $result;
    }

    /**
     * Get Tree list items as html list.
     *
     * @return Tree list html string
     *
     * @param string $dir            Current directory
     * @param bool   $root[optional] Is root directory
     * @param bool   $init[optional] Is tree initialisation
     */
    public function getTreeItems($dir, $root = true, $init = true)
    {
        $result = '';

        static $treedir = null;

        if ($init) {
            $treedir = $dir;
            if ($root) {
                $result = '<ul>'
                . '<li data-id="/" class="uk-tree-open uk-tree-root uk-padding-remove">'
                . ' <div class="uk-tree-row">'
                . '   <a href="#">'
                . '     <span class="uk-tree-icon" role="presentation">'
                . '       <i class="uk-icon uk-icon-home"></i>'
                . '     </span>'
                . '     <span class="uk-tree-text">' . JText::_('WF_LABEL_HOME', 'Home') . '</span>'
                    . '   </a>'
                    . ' </div>';

                $dir = '/';
            }
        }
        $folders = $this->getFolders($dir);

        if ($folders) {
            $result .= '<ul class="uk-tree-node">';
            foreach ($folders as $folder) {
                $name = ltrim($folder['id'], '/');

                $open = preg_match('#' . $name . '\b#', $treedir);

                $result .= '<li data-id="' . $this->escape($name) . '" class="' . ($open ? 'uk-tree-open' : '') . '">'
                    . ' <div class="uk-tree-row">'
                    . '   <a href="#">'
                    . '     <span class="uk-tree-icon" role="presentation"></span>'
                    . '     <span class="uk-tree-text uk-text-truncate" title="' . $folder['name'] . '">' . $folder['name'] . '</span>'
                    . '   </a>'
                    . ' </div>';

                if ($open) {
                    if ($h = $this->getTreeItems($folder['id'], false, false)) {
                        $result .= $h;
                    }
                }

                $result .= '</li>';
            }
            $result .= '</ul>';
        }
        if ($init && $root) {
            $result .= '</li></ul>';
        }
        $init = false;

        return $result;
    }

    /**
     * Get a folders properties.
     *
     * @return array Array of properties
     *
     * @param string $dir Folder relative path
     */
    public function getFolderDetails($dir)
    {
        WFUtility::checkPath($dir);

        $filesystem = $this->getFileSystem();
        // get array with folder date and content count eg: array('date'=>'00-00-000', 'folders'=>1, 'files'=>2);
        return $filesystem->getFolderDetails($dir);
    }

    /**
     * Get a files properties.
     *
     * @return array Array of properties
     *
     * @param string $file File relative path
     */
    public function getFileDetails($file)
    {
        WFUtility::checkPath($file);

        $filesystem = $this->getFileSystem();
        // get array with folder date and content count eg: array('date'=>'00-00-000', 'folders'=>1, 'files'=>2);
        return $filesystem->getFileDetails($file);
    }

    /**
     * Create default actions based on access.
     */
    private function addDefaultActions()
    {
        $this->addAction('help', array('title' => JText::_('WF_BUTTON_HELP')));

        if ($this->checkFeature('upload')) {
            $this->addAction('upload');
            $this->setRequest(array($this, 'upload'));
        }

        if ($this->checkFeature('create', 'folder')) {
            $this->addAction('folder_new');
            $this->setRequest(array($this, 'folderNew'));
        }
    }

    /**
     * Add an action to the list.
     *
     * @param string $name    Action name
     * @param array  $options Array of options
     */
    public function addAction($name, $options = array())
    {
        if (!is_array($options)) {
            list($name, $options['icon'], $options['action'], $options['title']) = func_get_args();
        }

        $options = array_merge(array('name' => $name), $options);

        // set some defaults
        if (!array_key_exists('icon', $options)) {
            $options['icon'] = '';
        }

        if (!array_key_exists('action', $options)) {
            $options['action'] = '';
        }

        if (!array_key_exists('title', $options)) {
            $options['title'] = JText::_('WF_BUTTON_' . strtoupper($name));
        }

        $this->_actions[$name] = $options;
    }

    /**
     * Get all actions.
     *
     * @return object
     */
    private function getActions()
    {
        return array_reverse($this->_actions);
    }

    /**
     * Remove an action from the list by name.
     *
     * @param string $name Action name to remove
     */
    public function removeAction($name)
    {
        if (isset($this->_actions[$name])) {
            unset($this->_actions[$name]);
        }
    }

    /**
     * Create all standard buttons based on access.
     */
    private function addDefaultButtons()
    {
        if ($this->checkFeature('delete', 'folder')) {
            $this->addButton('folder', 'delete', array('multiple' => true));

            $this->setRequest(array($this, 'deleteItem'));
        }
        if ($this->checkFeature('rename', 'folder')) {
            $this->addButton('folder', 'rename');

            $this->setRequest(array($this, 'renameItem'));
        }
        if ($this->checkFeature('move', 'folder')) {
            $this->addButton('folder', 'copy', array('multiple' => true));
            $this->addButton('folder', 'cut', array('multiple' => true));

            $this->addButton('folder', 'paste', array('multiple' => true, 'trigger' => true));

            $this->setRequest(array($this, 'copyItem'));
            $this->setRequest(array($this, 'moveItem'));
        }
        if ($this->checkFeature('rename', 'file')) {
            $this->addButton('file', 'rename');

            $this->setRequest(array($this, 'renameItem'));
        }
        if ($this->checkFeature('delete', 'file')) {
            $this->addButton('file', 'delete', array('multiple' => true));

            $this->setRequest(array($this, 'deleteItem'));
        }
        if ($this->checkFeature('move', 'file')) {
            $this->addButton('file', 'copy', array('multiple' => true));
            $this->addButton('file', 'cut', array('multiple' => true));

            $this->addButton('file', 'paste', array('multiple' => true, 'trigger' => true));

            $this->setRequest(array($this, 'copyItem'));
            $this->setRequest(array($this, 'moveItem'));
        }
        $this->addButton('file', 'view', array('restrict' => $this->getViewable()));
    }

    /**
     * Add a button.
     *
     * @param string $type[optional]     Button type (file or folder)
     * @param string $name               Button name
     * @param string $icon[optional]     Button icon
     * @param string $action[optional]   Button action / function
     * @param string $title              Button title
     * @param bool   $multiple[optional] Supports multiple file selection
     * @param bool   $trigger[optional]
     */
    public function addButton($type, $name, $options = array())
    {
        $options = array_merge(array('name' => $name), $options);

        // set some defaults
        if (!array_key_exists('icon', $options)) {
            $options['icon'] = '';
        }

        if (!array_key_exists('action', $options)) {
            $options['action'] = '';
        }

        if (!array_key_exists('title', $options)) {
            $options['title'] = JText::_('WF_BUTTON_' . strtoupper($name));
        }

        if (!array_key_exists('multiple', $options)) {
            $options['multiple'] = false;
        }

        if (!array_key_exists('trigger', $options)) {
            $options['trigger'] = false;
        }

        if (!array_key_exists('restrict', $options)) {
            $options['restrict'] = '';
        }

        $this->_buttons[$type][$name] = $options;
    }

    /**
     * Return an object list of all buttons.
     *
     * @return object
     */
    private function getButtons()
    {
        return $this->_buttons;
    }

    /**
     * Remove a button.
     *
     * @param string $type Button type
     * @param string $name Button name
     */
    public function removeButton($type, $name)
    {
        if (array_key_exists($name, $this->_buttons[$type])) {
            unset($this->_buttons[$type][$name]);
        }
    }

    /**
     * Change a buttons properties.
     *
     * @param string $type Button type
     * @param string $name Button name
     * @param string $keys Button keys
     */
    public function changeButton($type, $name, $keys)
    {
        foreach ($keys as $key => $value) {
            if (isset($this->_buttons[$type][$name][$key])) {
                $this->_buttons[$type][$name][$key] = $value;
            }
        }
    }

    /**
     * Add an event.
     *
     * @param string $name     Event name
     * @param string $function Event function name
     */
    public function addEvent($name, $function)
    {
        $this->_events[$name] = $function;
    }

    /**
     * Execute an event.
     *
     * @return array result
     *
     * @param object $name           Event name
     * @param array  $args[optional] Optional arguments
     */
    protected function fireEvent($name, $args = null)
    {
        if (array_key_exists($name, $this->_events)) {
            $event = $this->_events[$name];

            if (is_array($event)) {
                return call_user_func_array($event, $args);
            } else {
                return call_user_func($event, $args);
            }
        }

        return array();
    }

    /**
     * Get a file icon based on extension.
     *
     * @return string Path to file icon
     *
     * @param string $ext File extension
     */
    public function getFileIcon($ext)
    {
        if (JFile::exists(WF_EDITOR_LIBRARIES . '/img/icons/' . $ext . '.gif')) {
            return $this->image('libraries.icons/' . $ext . '.gif');
        } elseif (JFile::exists($this->getPluginPath() . '/img/icons/' . $ext . '.gif')) {
            return $this->image('plugins.icons/' . $ext . '.gif');
        } else {
            return $this->image('libraries.icons/def.gif');
        }
    }

    private function validateUploadedFile($file)
    {
        // check the POST data array
        if (empty($file) || empty($file['tmp_name'])) {
            throw new InvalidArgumentException('Upload Failed: No data');
        }

        // check for tmp_name and is valid uploaded file
        if (!is_uploaded_file($file['tmp_name'])) {
            @unlink($file['tmp_name']);
            throw new InvalidArgumentException('Upload Failed: Not an uploaded file');
        }

        $upload = $this->get('upload');

        // check file for various issues
        if (WFUtility::isSafeFile($file) !== true) {
            @unlink($file['tmp_name']);
            throw new InvalidArgumentException('Upload Failed: Invalid file');
        }

        // get extension
        $ext = WFUtility::getExtension($file['name']);

        // check extension is allowed
        $allowed = (array) $this->getFileTypes('array');

        if (is_array($allowed) && !empty($allowed) && in_array(strtolower($ext), $allowed) === false) {
            @unlink($file['tmp_name']);
            throw new InvalidArgumentException(JText::_('WF_MANAGER_UPLOAD_INVALID_EXT_ERROR'));
        }

        $size = round(filesize($file['tmp_name']) / 1024);

        if (empty($upload['max_size'])) {
            $upload['max_size'] = 1024;
        }

        // validate size
        if ($size > (int) $upload['max_size']) {
            @unlink($file['tmp_name']);

            throw new InvalidArgumentException(JText::sprintf('WF_MANAGER_UPLOAD_SIZE_ERROR', $file['name'], $size, $upload['max_size']));
        }

        // validate mimetype
        if ($upload['validate_mimetype']) {
            if (WFMimeType::check($file['name'], $file['tmp_name']) === false) {
                @unlink($file['tmp_name']);
                throw new InvalidArgumentException(JText::_('WF_MANAGER_UPLOAD_MIME_ERROR'));
            }
        }

        return true;
    }

    /**
     * Upload a file.
     *
     * @return array $error on failure or uploaded file name on success
     */
    public function upload()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // check for feature access
        if (!$this->checkFeature('upload')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $app = JFactory::getApplication();

        $filesystem = $this->getFileSystem();
        jimport('joomla.filesystem.file');

        // create a filesystem result object
        $result = new WFFileSystemResult();

        // get uploaded file
        $file = $app->input->files->get('file', array(), 'raw');

        // validate file
        $this->validateUploadedFile($file);

        // get file name
        $name = (string) $app->input->get('name', $file['name'], 'STRING');

        // decode
        $name = rawurldecode($name);

        // check name
        if (WFUtility::validateFileName($name) === false) {
            throw new InvalidArgumentException('Upload Failed: The file name contains an invalid extension.');
        }

        // check file name
        WFUtility::checkPath($name);

        // get extension from file name
        $ext = WFUtility::getExtension($file['name']);

        // trim extension
        $ext = trim($ext);

        // make extension websafe
        $ext = WFUtility::makeSafe($ext, $this->get('websafe_mode', 'utf-8'), $this->get('websafe_spaces'), $this->get('websafe_textcase'));

        // check extension exists
        if (empty($ext) || $ext === $file['name']) {
            throw new InvalidArgumentException('Upload Failed: The file name does not contain a valid extension.');
        }

        // strip extension
        $name = WFUtility::stripExtension($name);

        // make file name 'web safe'
        $name = WFUtility::makeSafe($name, $this->get('websafe_mode', 'utf-8'), $this->get('websafe_spaces'), $this->get('websafe_textcase'));

        // check name
        if (WFUtility::validateFileName($name) === false) {
            throw new InvalidArgumentException('Upload Failed: The file name contains an invalid extension.');
        }

        // target directory
        $dir = (string) $app->input->get('upload-dir', '', 'STRING');

        // decode and cast as string
        $dir = rawurldecode($dir);

        // check destination path
        WFUtility::checkPath($dir);

        // check access
        if (!$this->checkPathAccess($dir)) {
            throw new InvalidArgumentException('Upload Failed: Access to the target directory is restricted');
        }

        $upload = $this->get('upload');

        // Check file number limits
        if (!empty($upload['total_files'])) {
            if ($filesystem->countFiles($dir, true) > $upload['total_files']) {
                throw new InvalidArgumentException(JText::_('WF_MANAGER_FILE_LIMIT_ERROR'));
            }
        }

        // Check total file size limit
        if (!empty($upload['total_size'])) {
            $size = $filesystem->getTotalSize($dir);

            if (($size / 1024 / 1024) > $upload['total_size']) {
                throw new InvalidArgumentException(JText::_('WF_MANAGER_FILE_SIZE_LIMIT_ERROR'));
            }
        }

        // add random string
        if ($upload['add_random']) {
            $name = $name . '_' . substr(md5(uniqid(rand(), 1)), 0, 5);
        }

        // rebuild file name - name + extension
        $name = $name . '.' . $ext;

        $contentType = $_SERVER['CONTENT_TYPE'];

        // Only multipart uploading is supported for now
        if ($contentType && strpos($contentType, 'multipart') !== false) {

            // pass to onBeforeUpload
            $this->fireEvent('onBeforeUpload', array(&$file, &$dir, &$name));

            // upload file with filesystem
            $result = $filesystem->upload('multipart', trim($file['tmp_name']), $dir, $name);

            if (!$result->state) {
                if (empty($result->message)) {
                    $result->message = JText::_('WF_MANAGER_UPLOAD_ERROR');
                }

                $result->code = 103;
            }

            @unlink($file['tmp_name']);
        } else {
            $result->state = false;
            $result->code = 103;
            $result->message = JText::_('WF_MANAGER_UPLOAD_ERROR');
        }

        // upload finished
        if ($result instanceof WFFileSystemResult) {
            if ($result->state === true) {
                $name = basename($result->path);

                if (empty($result->url)) {
                    $result->url = WFUtility::makePath($filesystem->getRootDir(), WFUtility::makePath($dir, $name));
                }

                // run events
                $data = $this->fireEvent('onUpload', array($result->path, $result->url));

                $data['name'] = $name;

                $this->setResult($data, 'files');

            } else {
                $this->setResult($result->message, 'error');
            }
        }

        return $this->getResult();
    }

    /**
     * Delete the relative file(s).
     *
     * @param $files the relative path to the file name or comma seperated list of multiple paths
     *
     * @return string $error on failure
     */
    public function deleteItem($items)
    {
        // check for feature access
        if (!$this->checkFeature('delete', 'folder') && !$this->checkFeature('delete', 'file')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $filesystem = $this->getFileSystem();
        $items = explode(',', rawurldecode((string) $items));

        foreach ($items as $item) {
            // decode and cast as string
            $item = (string) rawurldecode($item);

            // check path
            WFUtility::checkPath($item);

            if ($filesystem->is_file($item)) {
                if ($this->checkFeature('delete', 'file') === false) {
                    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
                }

                $path = $item;
            } elseif ($filesystem->is_dir($item)) {
                if ($this->checkFeature('delete', 'folder') === false) {
                    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
                }

                $path = dirname($item);
            }

            // check access
            if (!$this->checkPathAccess($path)) {
                throw new InvalidArgumentException('Delete Failed: Access to the target directory is restricted');
            }

            $result = $filesystem->delete($item);

            if ($result instanceof WFFileSystemResult) {
                if (!$result->state) {
                    if ($result->message) {
                        $this->setResult($result->message, 'error');
                    } else {
                        $this->setResult(JText::sprintf('WF_MANAGER_DELETE_' . strtoupper($result->type) . '_ERROR', basename($item)), 'error');
                    }
                } else {
                    $this->fireEvent('on' . ucfirst($result->type) . 'Delete', array($item));
                    $this->setResult($item, $result->type);
                }
            }
        }

        return $this->getResult();
    }

    /**
     * Rename a file.
     *
     * @param string $src  The relative path of the source file
     * @param string $dest The name of the new file
     *
     * @return string $error
     */
    public function renameItem()
    {
        // check for feature access
        if (!$this->checkFeature('rename', 'folder') && !$this->checkFeature('rename', 'file')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $args = func_get_args();

        $source = array_shift($args);
        $destination = array_shift($args);

        // decode and cast as string
        $source = (string) rawurldecode($source);
        // decode and cast as string
        $destination = (string) rawurldecode($destination);

        WFUtility::checkPath($source);
        WFUtility::checkPath($destination);

        // check access
        if (!$this->checkPathAccess($destination)) {
            throw new InvalidArgumentException('Rename Failed: Access to the target directory is restricted');
        }

        // check for extension in destination name
        if (WFUtility::validateFileName($destination) === false) {
            throw new InvalidArgumentException('INVALID FILE NAME');
        }

        $filesystem = $this->getFileSystem();

        if ($filesystem->is_file($source)) {
            if ($this->checkFeature('rename', 'file') === false) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
            }

            $path = dirname($source);
        } elseif ($filesystem->is_dir($source)) {
            if ($this->checkFeature('rename', 'folder') === false) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
            }

            $path = $source;
        }

        // check access
        if (!$this->checkPathAccess($path)) {
            throw new InvalidArgumentException('Rename Failed: Access to the target directory is restricted');
        }

        // apply filesystem options
        $destination = WFUtility::makeSafe($destination, $this->get('websafe_mode'), $this->get('websafe_spaces'), $this->get('websafe_textcase'));
        $result = $filesystem->rename($source, $destination, $args);

        if ($result instanceof WFFileSystemResult) {
            if (!$result->state) {
                $this->setResult(JText::sprintf('WF_MANAGER_RENAME_' . strtoupper($result->type) . '_ERROR', basename($source)), 'error');
                if ($result->message) {
                    $this->setResult($result->message, 'error');
                }
            } else {
                $data = array(
                    'name' => basename($result->path)
                );
                
                $event = $this->fireEvent('on' . ucfirst($result->type) . 'Rename', array($destination));

                // merge event data with default values
                $data = array_merge($data, $event);

                $this->setResult($data, $result->type);
            }
        }

        return $this->getResult();
    }

    /**
     * Copy a file.
     *
     * @param string $files The relative file or comma seperated list of files
     * @param string $dest  The relative path of the destination dir
     *
     * @return string $error on failure
     */
    public function copyItem($items, $destination, $overwrite = false)
    {
        // check for feature access
        if (!$this->checkFeature('move', 'folder') && !$this->checkFeature('move', 'file')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $filesystem = $this->getFileSystem();

        $items = explode(',', rawurldecode((string) $items));

        // decode and cast as string
        $destination = (string) rawurldecode($destination);

        if (empty($destination)) {
            $destination = '/';
        }

        // check destination path
        WFUtility::checkPath($destination);

        // check access
        if (!$this->checkPathAccess($destination)) {
            throw new InvalidArgumentException('Copy Failed: Access to the target directory is restricted');
        }

        // check for extension in destination name
        if (WFUtility::validateFileName($destination) === false) {
            throw new InvalidArgumentException('INVALID PATH NAME');
        }

        foreach ($items as $item) {
            // decode and cast as string
            $item = (string) rawurldecode($item);

            // check source path
            WFUtility::checkPath($item);

            if ($filesystem->is_file($item)) {
                if ($this->checkFeature('move', 'file') === false) {
                    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
                }

                $path = dirname($item);
            } elseif ($filesystem->is_dir($item)) {
                if ($this->checkFeature('move', 'folder') === false) {
                    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
                }

                $path = $item;
            }

            if ($filesystem->is_file(WFUtility::makePath($destination, basename($item))) && $overwrite === false) {
                $this->setResult($item, 'confirm');

                return $this->getResult();
            }

            // check access
            if (!$this->checkPathAccess($path)) {
                throw new InvalidArgumentException('Copy Failed: Access to the target directory is restricted');
            }

            $result = $filesystem->copy($item, $destination);

            if ($result instanceof WFFileSystemResult) {
                if (!$result->state) {
                    if ($result->message) {
                        $this->setResult($result->message, 'error');
                    } else {
                        $this->setResult(JText::sprintf('WF_MANAGER_COPY_' . strtoupper($result->type) . '_ERROR', basename($item)), 'error');
                    }
                } else {
                    $data = array(
                        'name' => $filesystem->toRelative($result->path)
                    );
                    
                    $event = $this->fireEvent('on' . ucfirst($result->type) . 'Copy', array($item));

                    // merge event data with default values
                    $data = array_merge($data, $event);

                    $this->setResult($data, $result->type);
                }
            }
        }

        return $this->getResult();
    }

    /**
     * Copy a file.
     *
     * @param string $files The relative file or comma seperated list of files
     * @param string $dest  The relative path of the destination dir
     *
     * @return string $error on failure
     */
    public function moveItem($items, $destination, $overwrite = false)
    {
        // check for feature access
        if (!$this->checkFeature('move', 'folder') && !$this->checkFeature('move', 'file')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $filesystem = $this->getFileSystem();

        $items = explode(',', rawurldecode((string) $items));

        // decode and cast as string
        $destination = (string) rawurldecode($destination);

        if (empty($destination)) {
            $destination = '/';
        }

        // check destination path
        WFUtility::checkPath($destination);

        // check access
        if (!$this->checkPathAccess($destination)) {
            throw new InvalidArgumentException('Move Failed: Access to the target directory is restricted');
        }

        // check for extension in destination name
        if (WFUtility::validateFileName($destination) === false) {
            throw new InvalidArgumentException('INVALID PATH NAME');
        }

        foreach ($items as $item) {
            // decode and cast as string
            $item = (string) rawurldecode($item);
            // check source path
            WFUtility::checkPath($item);

            if ($filesystem->is_file($item)) {
                if ($this->checkFeature('move', 'file') === false) {
                    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
                }
            } elseif ($filesystem->is_dir($item)) {
                if ($this->checkFeature('move', 'folder') === false) {
                    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
                }
            }

            if ($filesystem->is_file(WFUtility::makePath($destination, basename($item))) && $overwrite === false) {
                $this->setResult($item, 'confirm');

                return $this->getResult();
            }

            $result = $filesystem->move($item, $destination);

            if ($result instanceof WFFileSystemResult) {
                if (!$result->state) {
                    if ($result->message) {
                        $this->setResult($result->message, 'error');
                    } else {
                        $this->setResult(JText::sprintf('WF_MANAGER_MOVE_' . strtoupper($result->type) . '_ERROR', basename($item)), 'error');
                    }
                } else {
                    $data = array(
                        'name' => $filesystem->toRelative($result->path)
                    );
                    
                    $event = $this->fireEvent('on' . ucfirst($result->type) . 'Move', array($item));

                    // merge event data with default values
                    $data = array_merge($data, $event);

                    $this->setResult($data, $result->type);
                }
            }
        }

        return $this->getResult();
    }

    /**
     * New folder.
     *
     * @param string $dir     The base dir
     * @param string $new_dir The folder to be created
     *
     * @return string $error on failure
     */
    public function folderNew()
    {
        if ($this->checkFeature('create', 'folder') === false) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $args = func_get_args();

        $dir = array_shift($args);
        $new = array_shift($args);

        // decode and cast as string
        $dir = (string) rawurldecode($dir);
        $new = (string) rawurldecode($new);

        // check access
        if (!$this->checkPathAccess($dir)) {
            throw new InvalidArgumentException('Action Failed: Access to the target directory is restricted');
        }

        $filesystem = $this->getFileSystem();

        $name = WFUtility::makeSafe($new, $this->get('websafe_mode'), $this->get('websafe_spaces'), $this->get('websafe_textcase'));

        // check for extension in destination name
        if (WFUtility::validateFileName($name) === false) {
            throw new InvalidArgumentException('INVALID FOLDER NAME');
        }

        $result = $filesystem->createFolder($dir, $name, $args);

        if ($result instanceof WFFileSystemResult) {
            if (!$result->state) {
                if ($result->message) {
                    $this->setResult($result->message, 'error');
                } else {
                    $this->setResult(JText::sprintf('WF_MANAGER_NEW_FOLDER_ERROR', basename($new)), 'error');
                }
            } else {
                $this->fireEvent('onFolderNew', array($new));
            }
        }

        return $this->getResult();
    }

    private function getUploadValue()
    {
        $upload = trim(ini_get('upload_max_filesize'));
        $post = trim(ini_get('post_max_size'));

        $upload = WFUtility::convertSize($upload);
        $post = WFUtility::convertSize($post);

        if (intval($upload) <= intval($post)) {
            return $upload;
        }

        return $post;
    }

    private function getUploadDefaults()
    {
        $filesystem = $this->getFileSystem();
        $features = $filesystem->get('upload');

        $upload_max = $this->getUploadValue();

        $upload = $this->get('upload');

        // get max size as kilobytes
        if (empty($upload['max_size'])) {
            $upload['max_size'] = 1024;
        }

        // get upload size as integer
        $size = intval(preg_replace('/[^0-9]/', '', $upload['max_size']));

        // must not exceed server maximum if > 0
        if (!empty($upload_max)) {
            if ((int) $size * 1024 > (int) $upload_max) {
                $size = $upload_max / 1024;
            }
        }

        $upload = array_merge($upload, array(
            'max_size' => $size,
            'filetypes' => $this->listFileTypes(),
        ));

        if (isset($features['elements'])) {
            $upload['elements'] = $features['elements'];
        }

        if (isset($features['dialog'])) {
            $upload['dialog'] = $features['dialog'];
        }

        return $upload;
    }

    public function getDimensions($file)
    {
        return $this->getFileSystem()->getDimensions($file);
    }

    // Set File Browser config
    private function setConfig($config = array())
    {
        // apply passed in properties (this must be done before initialising filesystem!)
        if (!empty($config)) {
            $this->setProperties($config);
        }

        $filesystem = $this->getFileSystem();

        $default = array(
            'upload' => $this->getUploadDefaults(),
        );

        $properties = array('base', 'delete', 'rename', 'folder_new', 'copy', 'move');

        foreach ($properties as $property) {
            if ($filesystem->get($property)) {
                $default[$property] = $filesystem->get($property);
            }
        }

        // apply default properties
        $this->setProperties($default);
    }
}
