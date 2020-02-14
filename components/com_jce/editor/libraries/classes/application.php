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

require_once(JPATH_ADMINISTRATOR . '/components/com_jce/includes/base.php');

/**
 * JCE class.
 *
 * @static
 *
 * @since    1.5
 */
class WFApplication extends JObject
{
    // Editor instance
    protected static $instance;

    // Editor Profile
    protected static $profile = array();

    // Editor Params
    protected static $params = array();

    // JInput Reference
    public $input;

    /**
     * Constructor activating the default information of the class.
     */
    public function __construct($config = array())
    {
        $this->setProperties($config);

        // store a reference to the Joomla Application input
        $this->input = JFactory::getApplication()->input;
    }

    /**
     * Returns a reference to a editor object.
     *
     * This method must be invoked as:
     *         <pre>  $browser =JContentEditor::getInstance();</pre>
     *
     * @return JCE The editor object
     */
    public static function getInstance($config = array())
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Get the current version.
     *
     * @return string
     */
    public function getVersion()
    {
        $manifest = WF_ADMINISTRATOR . '/jce.xml';

        $version = md5_file($manifest);

        return $version;
    }

    protected function getComponent($id = null, $option = null)
    {
        $component = JTable::getInstance('extension');

        // find component by option
        if (empty($id) && $option) {
            $id = $component->find(array('type' => 'component', 'element' => $option));
        }

        // load component
        $component->load($id);

        return $component;
    }

    public function getContext()
    {
        /*if ($this->profile) {
        // get token
        $token = JSession::getFormToken();
        // create context hash
        $this->context = md5($token . serialize($this->profile));
        // assign profile id to user session
        $app->setUserState($this->context, $this->profile->id);
        }*/

        $option = JFactory::getApplication()->input->getCmd('option');
        $extension = $this->getComponent(null, $option);

        $extension_id = 0;

        if (isset($extension->extension_id)) {
            return $extension->extension_id;
        }

        if (isset($extension->id)) {
            return $extension->id;
        }

        return 0;
    }

    private function getProfileVars($plugin = '')
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $option = $this->getComponentOption();

        if ($option == 'com_jce') {
            $context = $app->input->getInt('context');

            if ($context) {
                $component = $this->getComponent($context);
                $option = isset($component->element) ? $component->element : $component->option;
            }
        }

        // get the Joomla! area, default to "site"
        $area = $app->getClientId() === 0 ? 1 : 2;

        if (!class_exists('Wf_Mobile_Detect')) {
            // load mobile detect class
            require_once __DIR__ . '/mobile.php';
        }

        $mobile = new Wf_Mobile_Detect();

        // desktop - default
        $device = 'desktop';

        // phone
        if ($mobile->isMobile()) {
            $device = 'phone';
        }

        if ($mobile->isTablet()) {
            $device = 'tablet';
        }

        $groups = $user->getAuthorisedGroups();

        return array(
            'option' => $option,
            'area' => $area,
            'device' => $device,
            'groups' => $groups,
            'plugin' => $plugin,
        );
    }

    private function isCorePlugin($plugin)
    {
        return in_array($plugin, array('core', 'autolink', 'cleanup', 'code', 'format', 'importcss', 'colorpicker', 'upload', 'branding', 'inlinepopups', 'figure', 'ui'));
    }

    /**
     * Get an appropriate editor profile.
     */
    public function getProfile($plugin = '', $id = 0)
    {
        // reset the value if it is a core plugin
        if ($this->isCorePlugin($plugin)) {
            $plugin = '';
        }

        // get the profile variables for the current context
        $options = $this->getProfileVars($plugin);
        // create a signature to store
        $signature = serialize($options);

        if (!isset(self::$profile[$signature])) {
            $db = JFactory::getDBO();
            $user = JFactory::getUser();
            $app = JFactory::getApplication();

            $query = $db->getQuery(true);
            $query->select('*')->from('#__wf_profiles')->where('published = 1')->order('ordering ASC');

            if ($id) {
                $query->where('id = ' . (int) $id);
            }

            $db->setQuery($query);
            $profiles = $db->loadObjectList();

            if ($id && !empty($profiles)) {
                // assign profile
                self::$profile[$signature] = (object) $profiles[0];

                // return
                return self::$profile[$signature];
            }

            foreach ($profiles as $item) {
                // at least one user group or user must be set
                if (empty($item->types) && empty($item->users)) {
                    continue;
                }

                // check user groups - a value should always be set
                $groups = array_intersect($options['groups'], explode(',', $item->types));

                // user not in the current group...
                if (empty($groups)) {
                    // no additional users set or no user match
                    if (empty($item->users) || in_array($user->id, explode(',', $item->users)) === false) {
                        continue;
                    }
                }

                // check component
                if ($options['option'] !== 'com_jce' && $item->components && in_array($options['option'], explode(',', $item->components)) === false) {
                    continue;
                }

                // set device default as 'desktop,tablet,mobile'
                if (empty($item->device)) {
                    $item->device = 'desktop,tablet,phone';
                }

                // check device
                if (in_array($options['device'], explode(',', $item->device)) === false) {
                    continue;
                }

                // check area
                if (!empty($item->area) && (int) $item->area != $options['area']) {
                    continue;
                }

                if ($options['plugin'] && in_array($options['plugin'], explode(',', $item->plugins)) === false) {
                    continue;
                }

                // decrypt params
                if (!empty($item->params)) {
                    $item->params = JceEncryptHelper::decrypt($item->params);
                }

                // assign item to profile
                self::$profile[$signature] = (object) $item;

                // return
                return self::$profile[$signature];
            }

            return null;
        }

        return self::$profile[$signature];
    }

    /**
     * Get the component option.
     *
     * @return string
     */
    public function getComponentOption()
    {
        $app = JFactory::getApplication();

        $option = $app->input->getCmd('option', '');

        switch ($option) {
            case 'com_section':
                $option = 'com_content';
                break;
            case 'com_categories':
                $section = $app->input->getCmd('section');

                if ($section) {
                    $option = $section;
                }

                break;
        }

        return $option;
    }

    /**
     * Get editor parameters.
     *
     * @param array $options
     *
     * @return object
     */
    public function getParams($options = array())
    {
        $app = JFactory::getApplication();

        if (!isset(self::$params)) {
            self::$params = array();
        }

        // set blank key if not set
        if (!isset($options['key'])) {
            $options['key'] = '';
        }
        // set blank path if not set
        if (!isset($options['path'])) {
            $options['path'] = '';
        }

        // get plugin name
        $plugin = $app->input->getCmd('plugin');

        // optional caller, eg: Link
        $caller = '';

        // get name and caller from plugin name
        if (strpos($plugin, '.') !== false) {
            list($plugin, $caller) = explode('.', $plugin);

            if ($caller) {
                $options['caller'] = $caller;
            }
        }

        if ($plugin) {
            $options['plugin'] = $plugin;
        }

        $signature = serialize($options);

        if (empty(self::$params[$signature])) {
            // get plugin
            $editor = JPluginHelper::getPlugin('editors', 'jce');

            if (empty($editor->params)) {
                $editor->params = '{}';
            }

            // get editor params as an associative array
            $data1 = json_decode($editor->params, true);

            // if null or false, revert to array
            if (empty($data1)) {
                $data1 = array();
            }

            // assign params to "editor" key
            $data1 = array('editor' => $data1);

            // get params data for this profile
            $profile = $this->getProfile($plugin);

            // create empty default if no profile or params are set
            $params = empty($profile->params) ? '{}' : $profile->params;

            // get profile params as an associative array
            $data2 = json_decode($params, true);

            // if null or false, revert to array
            if (empty($data2)) {
                $data2 = array();
            }

            // merge params, but ignore empty values
            $data = WFUtility::array_merge_recursive_distinct($data1, $data2, true);

            // create new registry with params
            $params = new JRegistry($data);

            self::$params[$signature] = $params;
        }

        return self::$params[$signature];
    }

    private function isEmptyValue($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_array($value)) {
            return empty($value);
        }

        return false;
    }

    /**
     * Get a parameter by key.
     *
     * @param $key Parameter key eg: editor.width
     * @param $fallback Fallback value
     * @param $default Default value
     */
    public function getParam($key, $fallback = '', $default = '', $type = 'string')
    {
        // get params for base key
        $params = $this->getParams();

        // get a parameter
        $value = $params->get($key);

        // key not present in params or was empty string or empty array (JRegistry returns null), use fallback value
        if (self::isEmptyValue($value)) {            
            // set default as empty string
            $value = '';
            
            // key does not exist (parameter was not set) - use fallback
            if ($params->exists($key) === false) {
                $value = $fallback;

                // if fallback is empty, revert to system default if it is non-empty
                if ($fallback === '' && $default !== '') {
                    $value = $default;

                    // reset $default to prevent clearing
                    $default = '';
                }
            // parameter is set, but is empty, but fallback is not (inherited values)
            } else if ($fallback !== '') {
                $value = $fallback;
            }
        }

        // clean string value of whitespace
        if (is_string($value)) {
            $value = trim(preg_replace('#[\n\r\t]+#', '', $value));
        }

        // cast default to float if numeric
        if (is_numeric($default)) {
            $default = (float) $default;
        }

        // cast value to float if numeric
        if (is_numeric($value)) {
            $value = (float) $value;
        }

        // if value is equal to system default, clear $value and return
        if ($value === $default) {
            return '';
        }

        // cast value to boolean
        if ($type == 'boolean') {
            $value = (bool) $value;
        }

        return $value;
    }
}
