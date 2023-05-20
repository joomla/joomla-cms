<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Templates\Administrator\Helper\TemplateHelper;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Template model class.
 *
 * @since  1.6
 */
class TemplateModel extends FormModel
{
    /**
     * The information in a template
     *
     * @var    \stdClass
     * @since  1.6
     */
    protected $template = null;

    /**
     * The path to the template
     *
     * @var    string
     * @since  3.2
     */
    protected $element = null;

    /**
     * The path to the static assets
     *
     * @var    string
     * @since  4.1.0
     */
    protected $mediaElement = null;

    /**
     * Internal method to get file properties.
     *
     * @param   string  $path  The base path.
     * @param   string  $name  The file name.
     *
     * @return  object
     *
     * @since   1.6
     */
    protected function getFile($path, $name)
    {
        $temp = new \stdClass();

        if ($this->getTemplate()) {
            $path       = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . ($this->template->client_id === 0 ? 'site' : 'administrator') . DIRECTORY_SEPARATOR . $this->template->element, '', $path);
            $path       = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR . ($this->template->client_id === 0 ? '' : 'administrator' . DIRECTORY_SEPARATOR) . 'templates' . DIRECTORY_SEPARATOR . $this->template->element, '', $path);
            $temp->name = $name;
            $temp->id   = urlencode(base64_encode(str_replace('\\', '//', $path)));

            return $temp;
        }
    }

    /**
     * Method to store file information.
     *
     * @param   string    $path      The base path.
     * @param   string    $name      The file name.
     * @param   stdClass  $template  The std class object of template.
     *
     * @return  object  stdClass object.
     *
     * @since   4.0.0
     */
    protected function storeFileInfo($path, $name, $template)
    {
        $temp               = new \stdClass();
        $temp->id           = base64_encode($path . $name);
        $temp->client       = $template->client_id;
        $temp->template     = $template->element;
        $temp->extension_id = $template->extension_id;

        if ($coreFile = $this->getCoreFile($path . $name, $template->client_id)) {
            $temp->coreFile = md5_file($coreFile);
        } else {
            $temp->coreFile = null;
        }

        return $temp;
    }

    /**
     * Method to get all template list.
     *
     * @return  object  stdClass object
     *
     * @since   4.0.0
     */
    public function getTemplateList()
    {
        // Get a db connection.
        $db = $this->getDatabase();

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table
        $query->select(
            $this->getState(
                'list.select',
                'a.extension_id, a.name, a.element, a.client_id'
            )
        );

        $query->from($db->quoteName('#__extensions', 'a'))
            ->where($db->quoteName('a.enabled') . ' = 1')
            ->where($db->quoteName('a.type') . ' = ' . $db->quote('template'));

        // Reset the query.
        $db->setQuery($query);

        // Load the results as a list of stdClass objects.
        $results = $db->loadObjectList();

        return $results;
    }

    /**
     * Method to get all updated file list.
     *
     * @param   boolean  $state    The optional parameter if you want unchecked list.
     * @param   boolean  $all      The optional parameter if you want all list.
     * @param   boolean  $cleanup  The optional parameter if you want to clean record which is no more required.
     *
     * @return  object  stdClass object
     *
     * @since   4.0.0
     */
    public function getUpdatedList($state = false, $all = false, $cleanup = false)
    {
        // Get a db connection.
        $db = $this->getDatabase();

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table
        $query->select(
            $this->getState(
                'list.select',
                'a.template, a.hash_id, a.extension_id, a.state, a.action, a.client_id, a.created_date, a.modified_date'
            )
        );

        $template = $this->getTemplate();

        $query->from($db->quoteName('#__template_overrides', 'a'));

        if (!$all) {
            $teid = (int) $template->extension_id;
            $query->where($db->quoteName('extension_id') . ' = :teid')
                ->bind(':teid', $teid, ParameterType::INTEGER);
        }

        if ($state) {
            $query->where($db->quoteName('state') . ' = 0');
        }

        $query->order($db->quoteName('a.modified_date') . ' DESC');

        // Reset the query.
        $db->setQuery($query);

        // Load the results as a list of stdClass objects.
        $pks = $db->loadObjectList();

        if ($state) {
            return $pks;
        }

        $results = [];

        foreach ($pks as $pk) {
            $client = ApplicationHelper::getClientInfo($pk->client_id);
            $path   = Path::clean($client->path . '/templates/' . $pk->template . base64_decode($pk->hash_id));

            if (file_exists($path)) {
                $results[] = $pk;
            } elseif ($cleanup) {
                $cleanupIds   = [];
                $cleanupIds[] = $pk->hash_id;
                $this->publish($cleanupIds, -3, $pk->extension_id);
            }
        }

        return $results;
    }

    /**
     * Method to get a list of all the core files of override files.
     *
     * @return  array  An array of all core files.
     *
     * @since   4.0.0
     */
    public function getCoreList()
    {
        // Get list of all templates
        $templates = $this->getTemplateList();

        // Initialize the array variable to store core file list.
        $this->coreFileList = [];

        $app = Factory::getApplication();

        foreach ($templates as $template) {
            $client  = ApplicationHelper::getClientInfo($template->client_id);
            $element = Path::clean($client->path . '/templates/' . $template->element . '/');
            $path    = Path::clean($element . 'html/');

            if (is_dir($path)) {
                $this->prepareCoreFiles($path, $element, $template);
            }
        }

        // Sort list of stdClass array.
        usort(
            $this->coreFileList,
            function ($a, $b) {
                return strcmp($a->id, $b->id);
            }
        );

        return $this->coreFileList;
    }

    /**
     * Prepare core files.
     *
     * @param   string     $dir       The path of the directory to scan.
     * @param   string     $element   The path of the template element.
     * @param   \stdClass  $template  The stdClass object of template.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function prepareCoreFiles($dir, $element, $template)
    {
        $dirFiles = scandir($dir);

        foreach ($dirFiles as $key => $value) {
            if (in_array($value, ['.', '..', 'node_modules'])) {
                continue;
            }

            if (is_dir($dir . $value)) {
                $relativePath = str_replace($element, '', $dir . $value);
                $this->prepareCoreFiles($dir . $value . '/', $element, $template);
            } else {
                $ext           = pathinfo($dir . $value, PATHINFO_EXTENSION);
                $allowedFormat = $this->checkFormat($ext);

                if ($allowedFormat === true) {
                    $relativePath = str_replace($element, '', $dir);
                    $info         = $this->storeFileInfo('/' . $relativePath, $value, $template);

                    if ($info) {
                        $this->coreFileList[] = $info;
                    }
                }
            }
        }
    }

    /**
     * Method to update status of list.
     *
     * @param   array    $ids    The base path.
     * @param   array    $value  The file name.
     * @param   integer  $exid   The template extension id.
     *
     * @return  integer  Number of files changed.
     *
     * @since   4.0.0
     */
    public function publish($ids, $value, $exid)
    {
        $db = $this->getDatabase();

        foreach ($ids as $id) {
            if ($value === -3) {
                $deleteQuery = $db->getQuery(true)
                    ->delete($db->quoteName('#__template_overrides'))
                    ->where($db->quoteName('hash_id') . ' = :hashid')
                    ->where($db->quoteName('extension_id') . ' = :exid')
                    ->bind(':hashid', $id)
                    ->bind(':exid', $exid, ParameterType::INTEGER);

                try {
                    // Set the query using our newly populated query object and execute it.
                    $db->setQuery($deleteQuery);
                    $result = $db->execute();
                } catch (\RuntimeException $e) {
                    return $e;
                }
            } elseif ($value === 1 || $value === 0) {
                $updateQuery = $db->getQuery(true)
                    ->update($db->quoteName('#__template_overrides'))
                    ->set($db->quoteName('state') . ' = :state')
                    ->where($db->quoteName('hash_id') . ' = :hashid')
                    ->where($db->quoteName('extension_id') . ' = :exid')
                    ->bind(':state', $value, ParameterType::INTEGER)
                    ->bind(':hashid', $id)
                    ->bind(':exid', $exid, ParameterType::INTEGER);

                try {
                    // Set the query using our newly populated query object and execute it.
                    $db->setQuery($updateQuery);
                    $result = $db->execute();
                } catch (\RuntimeException $e) {
                    return $e;
                }
            }
        }

        return $result;
    }

    /**
     * Method to get a list of all the files to edit in a template.
     *
     * @return  array  A nested array of relevant files.
     *
     * @since   1.6
     */
    public function getFiles()
    {
        $result = [];

        if ($template = $this->getTemplate()) {
            $app    = Factory::getApplication();
            $client = ApplicationHelper::getClientInfo($template->client_id);
            $path   = Path::clean($client->path . '/templates/' . $template->element . '/');
            $lang   = Factory::getLanguage();

            // Load the core and/or local language file(s).
            $lang->load('tpl_' . $template->element, $client->path)
            || (!empty($template->xmldata->parent) && $lang->load('tpl_' . $template->xmldata->parent, $client->path))
            || $lang->load('tpl_' . $template->element, $client->path . '/templates/' . $template->element)
            || (!empty($template->xmldata->parent) && $lang->load('tpl_' . $template->xmldata->parent, $client->path . '/templates/' . $template->xmldata->parent));
            $this->element = $path;

            if (!is_writable($path)) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_DIRECTORY_NOT_WRITABLE'), 'error');
            }

            if (is_dir($path)) {
                $result = $this->getDirectoryTree($path);
            } else {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_TEMPLATE_FOLDER_NOT_FOUND'), 'error');

                return false;
            }

            // Clean up override history
            $this->getUpdatedList(false, true, true);
        }

        return $result;
    }

    /**
     * Get the directory tree.
     *
     * @param   string  $dir  The path of the directory to scan
     *
     * @return  array
     *
     * @since   3.2
     */
    public function getDirectoryTree($dir)
    {
        $result = [];

        $dirFiles = scandir($dir);

        foreach ($dirFiles as $key => $value) {
            if (!in_array($value, ['.', '..', 'node_modules'])) {
                if (is_dir($dir . $value)) {
                    $relativePath                                   = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . ($this->template->client_id === 0 ? 'site' : 'administrator') . DIRECTORY_SEPARATOR . $this->template->element, '', $dir . $value);
                    $relativePath                                   = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR . ($this->template->client_id === 0 ? '' : 'administrator' . DIRECTORY_SEPARATOR) . 'templates' . DIRECTORY_SEPARATOR . $this->template->element, '', $relativePath);
                    $result[str_replace('\\', '//', $relativePath)] = $this->getDirectoryTree($dir . $value . '/');
                } else {
                    $ext           = pathinfo($dir . $value, PATHINFO_EXTENSION);
                    $allowedFormat = $this->checkFormat($ext);

                    if ($allowedFormat == true) {
                        $relativePath = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'templates'  . DIRECTORY_SEPARATOR . ($this->template->client_id === 0 ? 'site' : 'administrator') . DIRECTORY_SEPARATOR . $this->template->element, '', $dir . $value);
                        $relativePath = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR . ($this->template->client_id === 0 ? '' : 'administrator' . DIRECTORY_SEPARATOR) . 'templates' . DIRECTORY_SEPARATOR . $this->template->element, '', $relativePath);
                        $result[]     = $this->getFile($relativePath, $value);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Method to get the core file of override file
     *
     * @param   string   $file       Override file
     * @param   integer  $client_id  Client Id
     *
     * @return  string  $corefile The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
     *
     * @since   4.0.0
     */
    public function getCoreFile($file, $client_id)
    {
        $app          = Factory::getApplication();
        $filePath     = Path::clean($file);
        $explodeArray = explode(DIRECTORY_SEPARATOR, $filePath);

        // Only allow html/ folder
        if ($explodeArray['1'] !== 'html') {
            return false;
        }

        $fileName = basename($filePath);
        $type     = $explodeArray['2'];
        $client   = ApplicationHelper::getClientInfo($client_id);

        $componentPath = Path::clean($client->path . '/components/');
        $modulePath    = Path::clean($client->path . '/modules/');
        $layoutPath    = Path::clean(JPATH_ROOT . '/layouts/');

        // For modules
        if (stristr($type, 'mod_') !== false) {
            $folder   = $explodeArray['2'];
            $htmlPath = Path::clean($modulePath . $folder . '/tmpl/');
            $fileName = $this->getSafeName($fileName);
            $coreFile = Path::find($htmlPath, $fileName);

            return $coreFile;
        } elseif (stristr($type, 'com_') !== false) {
            // For components
            $folder    = $explodeArray['2'];
            $subFolder = $explodeArray['3'];
            $fileName  = $this->getSafeName($fileName);

            // The new scheme, if a view has a tmpl folder
            $newHtmlPath = Path::clean($componentPath . $folder . '/tmpl/' . $subFolder . '/');

            if (!$coreFile = Path::find($newHtmlPath, $fileName)) {
                // The old scheme, the views are directly in the component/tmpl folder
                $oldHtmlPath = Path::clean($componentPath . $folder . '/views/' . $subFolder . '/tmpl/');
                $coreFile    = Path::find($oldHtmlPath, $fileName);

                return $coreFile;
            }

            return $coreFile;
        } elseif (stristr($type, 'layouts') !== false) {
            // For Layouts
            $subtype = $explodeArray['3'];

            if (stristr($subtype, 'com_')) {
                $folder    = $explodeArray['3'];
                $subFolder = array_slice($explodeArray, 4, -1);
                $subFolder = implode(DIRECTORY_SEPARATOR, $subFolder);
                $htmlPath  = Path::clean($componentPath . $folder . '/layouts/' . $subFolder);
                $fileName  = $this->getSafeName($fileName);
                $coreFile  = Path::find($htmlPath, $fileName);

                return $coreFile;
            } elseif (stristr($subtype, 'joomla') || stristr($subtype, 'libraries') || stristr($subtype, 'plugins')) {
                $subFolder = array_slice($explodeArray, 3, -1);
                $subFolder = implode(DIRECTORY_SEPARATOR, $subFolder);
                $htmlPath  = Path::clean($layoutPath . $subFolder);
                $fileName  = $this->getSafeName($fileName);
                $coreFile  = Path::find($htmlPath, $fileName);

                return $coreFile;
            }
        }

        return false;
    }

    /**
     * Creates a safe file name for the given name.
     *
     * @param   string  $name  The filename
     *
     * @return  string $fileName  The filtered name without Date
     *
     * @since   4.0.0
     */
    private function getSafeName($name)
    {
        if (strpos($name, '-') !== false && preg_match('/[0-9]/', $name)) {
            // Get the extension
            $extension = File::getExt($name);

            // Remove ( Date ) from file
            $explodeArray = explode('-', $name);
            $size         = count($explodeArray);
            $date         = $explodeArray[$size - 2] . '-' . str_replace('.' . $extension, '', $explodeArray[$size - 1]);

            if ($this->validateDate($date)) {
                $nameWithoutExtension = implode('-', array_slice($explodeArray, 0, -2));

                // Filtered name
                $name = $nameWithoutExtension . '.' . $extension;
            }
        }

        return $name;
    }

    /**
     * Validate Date in file name.
     *
     * @param   string  $date  Date to validate.
     *
     * @return boolean Return true if date is valid and false if not.
     *
     * @since  4.0.0
     */
    private function validateDate($date)
    {
        $format = 'Ymd-His';
        $valid  = Date::createFromFormat($format, $date);

        return $valid && $valid->format($format) === $date;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load the User state.
        $pk = $app->getInput()->getInt('id');
        $this->setState('extension.id', $pk);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_templates');
        $this->setState('params', $params);
    }

    /**
     * Method to get the template information.
     *
     * @return  mixed  Object if successful, false if not and internal error is set.
     *
     * @since   1.6
     */
    public function &getTemplate()
    {
        if (empty($this->template)) {
            $pk  = (int) $this->getState('extension.id');
            $db  = $this->getDatabase();
            $app = Factory::getApplication();

            // Get the template information.
            $query = $db->getQuery(true)
                ->select($db->quoteName(['extension_id', 'client_id', 'element', 'name', 'manifest_cache']))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('extension_id') . ' = :pk')
                ->where($db->quoteName('type') . ' = ' . $db->quote('template'))
                ->bind(':pk', $pk, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $result = $db->loadObject();
            } catch (\RuntimeException $e) {
                $app->enqueueMessage($e->getMessage(), 'warning');
                $this->template = false;

                return false;
            }

            if (empty($result)) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_EXTENSION_RECORD_NOT_FOUND'), 'error');
                $this->template = false;
            } else {
                $this->template = $result;

                // Client ID is not always an integer, so enforce here
                $this->template->client_id = (int) $this->template->client_id;

                if (!isset($this->template->xmldata)) {
                    $this->template->xmldata = TemplatesHelper::parseXMLTemplateFile($this->template->client_id === 0 ? JPATH_ROOT : JPATH_ROOT . '/administrator', $this->template->name);
                }
            }
        }

        return $this->template;
    }

    /**
     * Method to check if new template name already exists
     *
     * @return  boolean   true if name is not used, false otherwise
     *
     * @since   2.5
     */
    public function checkNewName()
    {
        $db    = $this->getDatabase();
        $name  = $this->getState('new_name');
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('name') . ' = :name')
            ->bind(':name', $name);
        $db->setQuery($query);

        return ($db->loadResult() == 0);
    }

    /**
     * Method to check if new template name already exists
     *
     * @return  string     name of current template
     *
     * @since   2.5
     */
    public function getFromName()
    {
        return $this->getTemplate()->element;
    }

    /**
     * Method to check if new template name already exists
     *
     * @return  boolean   true if name is not used, false otherwise
     *
     * @since   2.5
     */
    public function copy()
    {
        $app = Factory::getApplication();

        if ($template = $this->getTemplate()) {
            $client   = ApplicationHelper::getClientInfo($template->client_id);
            $fromPath = Path::clean($client->path . '/templates/' . $template->element . '/');

            // Delete new folder if it exists
            $toPath = $this->getState('to_path');

            if (Folder::exists($toPath)) {
                if (!Folder::delete($toPath)) {
                    $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_WRITE'), 'error');

                    return false;
                }
            }

            // Copy all files from $fromName template to $newName folder
            if (!Folder::copy($fromPath, $toPath)) {
                return false;
            }

            // Check manifest for additional files
            $manifest = simplexml_load_file($toPath . '/templateDetails.xml');

            // Copy language files from global folder
            if ($languages = $manifest->languages) {
                $folder        = (string) $languages->attributes()->folder;
                $languageFiles = $languages->language;

                Folder::create($toPath . '/' . $folder . '/' . $languageFiles->attributes()->tag);

                foreach ($languageFiles as $languageFile) {
                    $src = Path::clean($client->path . '/language/' . $languageFile);
                    $dst = Path::clean($toPath . '/' . $folder . '/' . $languageFile);

                    if (File::exists($src)) {
                        File::copy($src, $dst);
                    }
                }
            }

            // Copy media files
            if ($media = $manifest->media) {
                $folder      = (string) $media->attributes()->folder;
                $destination = (string) $media->attributes()->destination;

                Folder::copy(JPATH_SITE . '/media/' . $destination, $toPath . '/' . $folder);
            }

            // Adjust to new template name
            if (!$this->fixTemplateName()) {
                return false;
            }

            return true;
        } else {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'), 'error');

            return false;
        }
    }

    /**
     * Method to delete tmp folder
     *
     * @return  boolean   true if delete successful, false otherwise
     *
     * @since   2.5
     */
    public function cleanup()
    {
        // Clear installation messages
        $app = Factory::getApplication();
        $app->setUserState('com_installer.message', '');
        $app->setUserState('com_installer.extension_message', '');

        // Delete temporary directory
        return Folder::delete($this->getState('to_path'));
    }

    /**
     * Method to rename the template in the XML files and rename the language files
     *
     * @return  boolean  true if successful, false otherwise
     *
     * @since   2.5
     */
    protected function fixTemplateName()
    {
        // Rename Language files
        // Get list of language files
        $result   = true;
        $files    = Folder::files($this->getState('to_path'), '\.ini$', true, true);
        $newName  = strtolower($this->getState('new_name'));
        $template = $this->getTemplate();
        $oldName  = $template->element;
        $manifest = json_decode($template->manifest_cache);

        foreach ($files as $file) {
            $newFile = '/' . str_replace($oldName, $newName, basename($file));
            $result  = File::move($file, dirname($file) . $newFile) && $result;
        }

        // Edit XML file
        $xmlFile = $this->getState('to_path') . '/templateDetails.xml';

        if (File::exists($xmlFile)) {
            $contents  = file_get_contents($xmlFile);
            $pattern[] = '#<name>\s*' . $manifest->name . '\s*</name>#i';
            $replace[] = '<name>' . $newName . '</name>';
            $pattern[] = '#<language(.*)' . $oldName . '(.*)</language>#';
            $replace[] = '<language${1}' . $newName . '${2}</language>';
            $pattern[] = '#<media(.*)' . $oldName . '(.*)>#';
            $replace[] = '<media${1}' . $newName . '${2}>';
            $contents  = preg_replace($pattern, $replace, $contents);
            $result    = File::write($xmlFile, $contents) && $result;
        }

        return $result;
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean    A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        $app = Factory::getApplication();

        // Codemirror or Editor None should be enabled
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__extensions as a')
            ->where(
                '(a.name =' . $db->quote('plg_editors_codemirror') .
                ' AND a.enabled = 1) OR (a.name =' .
                $db->quote('plg_editors_none') .
                ' AND a.enabled = 1)'
            );
        $db->setQuery($query);
        $state = $db->loadResult();

        if ((int) $state < 1) {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_EDITOR_DISABLED'), 'warning');
        }

        // Get the form.
        $form = $this->loadForm('com_templates.source', 'source', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        $data = $this->getSource();

        $this->preprocessData('com_templates.source', $data);

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @return  mixed  Object on success, false on failure.
     *
     * @since   1.6
     */
    public function &getSource()
    {
        $app  = Factory::getApplication();
        $item = new \stdClass();

        if (!$this->template) {
            $this->getTemplate();
        }

        if ($this->template) {
            $input    = Factory::getApplication()->getInput();
            $fileName = base64_decode($input->get('file'));
            $fileName = str_replace('//', '/', $fileName);
            $isMedia  = $input->getInt('isMedia', 0);

            $fileName = $isMedia ? Path::clean(JPATH_ROOT . '/media/templates/' . ($this->template->client_id === 0 ? 'site' : 'administrator') . '/' . $this->template->element . $fileName)
            : Path::clean(JPATH_ROOT . ($this->template->client_id === 0 ? '' : '/administrator') . '/templates/' . $this->template->element . $fileName);

            try {
                $filePath = Path::check($fileName);
            } catch (\Exception $e) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'), 'error');

                return;
            }

            if (file_exists($filePath)) {
                $item->extension_id = $this->getState('extension.id');
                $item->filename     = Path::clean($fileName);
                $item->source       = file_get_contents($filePath);
                $item->filePath     = Path::clean($filePath);
                $ds                 = DIRECTORY_SEPARATOR;
                $cleanFileName      = str_replace(JPATH_ROOT . ($this->template->client_id === 1 ? $ds . 'administrator' . $ds : $ds) . 'templates' . $ds . $this->template->element, '', $fileName);

                if ($coreFile = $this->getCoreFile($cleanFileName, $this->template->client_id)) {
                    $item->coreFile = $coreFile;
                    $item->core     = file_get_contents($coreFile);
                }
            } else {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'), 'error');
            }
        }

        return $item;
    }

    /**
     * Method to store the source file contents.
     *
     * @param   array  $data  The source data to save.
     *
     * @return  boolean  True on success, false otherwise and internal error set.
     *
     * @since   1.6
     */
    public function save($data)
    {
        // Get the template.
        $template = $this->getTemplate();

        if (empty($template)) {
            return false;
        }

        $app      = Factory::getApplication();
        $fileName = base64_decode($app->getInput()->get('file'));
        $isMedia  = $app->getInput()->getInt('isMedia', 0);
        $fileName = $isMedia ? JPATH_ROOT . '/media/templates/' . ($this->template->client_id === 0 ? 'site' : 'administrator') . '/' . $this->template->element . $fileName :
            JPATH_ROOT . '/' . ($this->template->client_id === 0 ? '' : 'administrator/') . 'templates/' . $this->template->element . $fileName;

        $filePath = Path::clean($fileName);

        // Include the extension plugins for the save events.
        PluginHelper::importPlugin('extension');

        $user = get_current_user();
        chown($filePath, $user);
        Path::setPermissions($filePath, '0644');

        // Try to make the template file writable.
        if (!is_writable($filePath)) {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_WRITABLE'), 'warning');
            $app->enqueueMessage(Text::sprintf('COM_TEMPLATES_FILE_PERMISSIONS', Path::getPermissions($filePath)), 'warning');

            if (!Path::isOwner($filePath)) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_CHECK_FILE_OWNERSHIP'), 'warning');
            }

            return false;
        }

        // Make sure EOL is Unix
        $data['source'] = str_replace(["\r\n", "\r"], "\n", $data['source']);

        // If the asset file for the template ensure we have valid template so we don't instantly destroy it
        if ($fileName === '/joomla.asset.json' && json_decode($data['source']) === null) {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_ASSET_FILE_INVALID_JSON'));

            return false;
        }

        $return = File::write($filePath, $data['source']);

        if (!$return) {
            $app->enqueueMessage(Text::sprintf('COM_TEMPLATES_ERROR_FAILED_TO_SAVE_FILENAME', $fileName), 'error');

            return false;
        }

        // Get the extension of the changed file.
        $explodeArray = explode('.', $fileName);
        $ext          = end($explodeArray);

        if ($ext == 'less') {
            $app->enqueueMessage(Text::sprintf('COM_TEMPLATES_COMPILE_LESS', $fileName));
        }

        return true;
    }

    /**
     * Get overrides folder.
     *
     * @param   string  $name  The name of override.
     * @param   string  $path  Location of override.
     *
     * @return  object  containing override name and path.
     *
     * @since   3.2
     */
    public function getOverridesFolder($name, $path)
    {
        $folder       = new \stdClass();
        $folder->name = $name;
        $folder->path = base64_encode($path . $name);

        return $folder;
    }

    /**
     * Get a list of overrides.
     *
     * @return  array containing overrides.
     *
     * @since   3.2
     */
    public function getOverridesList()
    {
        if ($template = $this->getTemplate()) {
            $client        = ApplicationHelper::getClientInfo($template->client_id);
            $componentPath = Path::clean($client->path . '/components/');
            $modulePath    = Path::clean($client->path . '/modules/');
            $pluginPath    = Path::clean(JPATH_ROOT . '/plugins/');
            $layoutPath    = Path::clean(JPATH_ROOT . '/layouts/');
            $components    = Folder::folders($componentPath);

            foreach ($components as $component) {
                // Collect the folders with views
                $folders = Folder::folders($componentPath . '/' . $component, '^view[s]?$', false, true);
                $folders = array_merge($folders, Folder::folders($componentPath . '/' . $component, '^tmpl?$', false, true));

                if (!$folders) {
                    continue;
                }

                foreach ($folders as $folder) {
                    // The subfolders are views
                    $views = Folder::folders($folder);

                    foreach ($views as $view) {
                        // The old scheme, if a view has a tmpl folder
                        $path = $folder . '/' . $view . '/tmpl';

                        // The new scheme, the views are directly in the component/tmpl folder
                        if (!is_dir($path) && substr($folder, -4) == 'tmpl') {
                            $path = $folder . '/' . $view;
                        }

                        // Check if the folder exists
                        if (!is_dir($path)) {
                            continue;
                        }

                        $result['components'][$component][] = $this->getOverridesFolder($view, Path::clean($folder . '/'));
                    }
                }
            }

            foreach (Folder::folders($pluginPath) as $pluginGroup) {
                foreach (Folder::folders($pluginPath . '/' . $pluginGroup) as $plugin) {
                    if (file_exists($pluginPath . '/' . $pluginGroup . '/' . $plugin . '/tmpl/')) {
                        $pluginLayoutPath                  = Path::clean($pluginPath . '/' . $pluginGroup . '/');
                        $result['plugins'][$pluginGroup][] = $this->getOverridesFolder($plugin, $pluginLayoutPath);
                    }
                }
            }

            $modules = Folder::folders($modulePath);

            foreach ($modules as $module) {
                $result['modules'][] = $this->getOverridesFolder($module, $modulePath);
            }

            $layoutFolders = Folder::folders($layoutPath);

            foreach ($layoutFolders as $layoutFolder) {
                $layoutFolderPath = Path::clean($layoutPath . '/' . $layoutFolder . '/');
                $layouts          = Folder::folders($layoutFolderPath);

                foreach ($layouts as $layout) {
                    $result['layouts'][$layoutFolder][] = $this->getOverridesFolder($layout, $layoutFolderPath);
                }
            }

            // Check for layouts in component folders
            foreach ($components as $component) {
                if (file_exists($componentPath . '/' . $component . '/layouts/')) {
                    $componentLayoutPath = Path::clean($componentPath . '/' . $component . '/layouts/');

                    if ($componentLayoutPath) {
                        $layouts = Folder::folders($componentLayoutPath);

                        foreach ($layouts as $layout) {
                            $result['layouts'][$component][] = $this->getOverridesFolder($layout, $componentLayoutPath);
                        }
                    }
                }
            }
        }

        if (!empty($result)) {
            return $result;
        }
    }

    /**
     * Create overrides.
     *
     * @param   string  $override  The override location.
     *
     * @return   boolean  true if override creation is successful, false otherwise
     *
     * @since   3.2
     */
    public function createOverride($override)
    {
        if ($template = $this->getTemplate()) {
            $app          = Factory::getApplication();
            $explodeArray = explode(DIRECTORY_SEPARATOR, $override);
            $name         = end($explodeArray);
            $client       = ApplicationHelper::getClientInfo($template->client_id);

            if (stristr($name, 'mod_') != false) {
                $htmlPath   = Path::clean($client->path . '/templates/' . $template->element . '/html/' . $name);
            } elseif (stristr($override, 'com_') != false) {
                $size = count($explodeArray);

                $url = Path::clean($explodeArray[$size - 3] . '/' . $explodeArray[$size - 1]);

                if ($explodeArray[$size - 2] == 'layouts') {
                    $htmlPath = Path::clean($client->path . '/templates/' . $template->element . '/html/layouts/' . $url);
                } else {
                    $htmlPath = Path::clean($client->path . '/templates/' . $template->element . '/html/' . $url);
                }
            } elseif (stripos($override, Path::clean(JPATH_ROOT . '/plugins/')) === 0) {
                $size       = count($explodeArray);
                $layoutPath = Path::clean('plg_' . $explodeArray[$size - 2] . '_' . $explodeArray[$size - 1]);
                $htmlPath   = Path::clean($client->path . '/templates/' . $template->element . '/html/' . $layoutPath);
            } else {
                $layoutPath = implode('/', array_slice($explodeArray, -2));
                $htmlPath   = Path::clean($client->path . '/templates/' . $template->element . '/html/layouts/' . $layoutPath);
            }

            // Check Html folder, create if not exist
            if (!Folder::exists($htmlPath)) {
                if (!Folder::create($htmlPath)) {
                    $app->enqueueMessage(Text::_('COM_TEMPLATES_FOLDER_ERROR'), 'error');

                    return false;
                }
            }

            if (stristr($name, 'mod_') != false) {
                $return = $this->createTemplateOverride(Path::clean($override . '/tmpl'), $htmlPath);
            } elseif (stristr($override, 'com_') != false && stristr($override, 'layouts') == false) {
                $path = $override . '/tmpl';

                // View can also be in the top level folder
                if (!is_dir($path)) {
                    $path = $override;
                }

                $return = $this->createTemplateOverride(Path::clean($path), $htmlPath);
            } elseif (stripos($override, Path::clean(JPATH_ROOT . '/plugins/')) === 0) {
                $return = $this->createTemplateOverride(Path::clean($override . '/tmpl'), $htmlPath);
            } else {
                $return = $this->createTemplateOverride($override, $htmlPath);
            }

            if ($return) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_OVERRIDE_CREATED') . str_replace(JPATH_ROOT, '', $htmlPath));

                return true;
            } else {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_OVERRIDE_FAILED'), 'error');

                return false;
            }
        }
    }

    /**
     * Create override folder & file
     *
     * @param   string  $overridePath  The override location
     * @param   string  $htmlPath      The html location
     *
     * @return  boolean                True on success. False otherwise.
     */
    public function createTemplateOverride($overridePath, $htmlPath)
    {
        $return = false;

        if (empty($overridePath) || empty($htmlPath)) {
            return $return;
        }

        // Get list of template folders
        $folders = Folder::folders($overridePath, null, true, true);

        if (!empty($folders)) {
            foreach ($folders as $folder) {
                $htmlFolder = $htmlPath . str_replace($overridePath, '', $folder);

                if (!Folder::exists($htmlFolder)) {
                    Folder::create($htmlFolder);
                }
            }
        }

        // Get list of template files (Only get *.php file for template file)
        $files = Folder::files($overridePath, '.php', true, true);

        if (empty($files)) {
            return true;
        }

        foreach ($files as $file) {
            $overrideFilePath = str_replace($overridePath, '', $file);
            $htmlFilePath     = $htmlPath . $overrideFilePath;

            if (File::exists($htmlFilePath)) {
                // Generate new unique file name base on current time
                $today        = Factory::getDate();
                $htmlFilePath = File::stripExt($htmlFilePath) . '-' . $today->format('Ymd-His') . '.' . File::getExt($htmlFilePath);
            }

            $return = File::copy($file, $htmlFilePath, '', true);
        }

        return $return;
    }

    /**
     * Delete a particular file.
     *
     * @param   string  $file  The relative location of the file.
     *
     * @return   boolean  True if file deletion is successful, false otherwise
     *
     * @since   3.2
     */
    public function deleteFile($file)
    {
        if ($this->getTemplate()) {
            $app      = Factory::getApplication();
            $filePath = $this->getBasePath() . urldecode(base64_decode($file));

            $return = File::delete($filePath);

            if (!$return) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_DELETE_ERROR'), 'error');

                return false;
            }

            return true;
        }
    }

    /**
     * Create new file.
     *
     * @param   string  $name      The name of file.
     * @param   string  $type      The extension of the file.
     * @param   string  $location  Location for the new file.
     *
     * @return  boolean  true if file created successfully, false otherwise
     *
     * @since   3.2
     */
    public function createFile($name, $type, $location)
    {
        if ($this->getTemplate()) {
            $app  = Factory::getApplication();
            $base = $this->getBasePath();

            if (file_exists(Path::clean($base . '/' . $location . '/' . $name . '.' . $type))) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_EXISTS'), 'error');

                return false;
            }

            if (!fopen(Path::clean($base . '/' . $location . '/' . $name . '.' . $type), 'x')) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_CREATE_ERROR'), 'error');

                return false;
            }

            // Check if the format is allowed and will be showed in the backend
            $check = $this->checkFormat($type);

            // Add a message if we are not allowed to show this file in the backend.
            if (!$check) {
                $app->enqueueMessage(Text::sprintf('COM_TEMPLATES_WARNING_FORMAT_WILL_NOT_BE_VISIBLE', $type), 'warning');
            }

            return true;
        }
    }

    /**
     * Upload new file.
     *
     * @param   array   $file      The uploaded file array.
     * @param   string  $location  Location for the new file.
     *
     * @return   boolean  True if file uploaded successfully, false otherwise
     *
     * @since   3.2
     */
    public function uploadFile($file, $location)
    {
        if ($this->getTemplate()) {
            $app      = Factory::getApplication();
            $path     = $this->getBasePath();
            $fileName = File::makeSafe($file['name']);

            $err = null;

            if (!TemplateHelper::canUpload($file, $err)) {
                // Can't upload the file
                return false;
            }

            if (file_exists(Path::clean($path . '/' . $location . '/' . $file['name']))) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_EXISTS'), 'error');

                return false;
            }

            if (!File::upload($file['tmp_name'], Path::clean($path . '/' . $location . '/' . $fileName))) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_UPLOAD_ERROR'), 'error');

                return false;
            }

            $url = Path::clean($location . '/' . $fileName);

            return $url;
        }
    }

    /**
     * Create new folder.
     *
     * @param   string  $name      The name of the new folder.
     * @param   string  $location  Location for the new folder.
     *
     * @return   boolean  True if override folder is created successfully, false otherwise
     *
     * @since   3.2
     */
    public function createFolder($name, $location)
    {
        if ($this->getTemplate()) {
            $app    = Factory::getApplication();
            $path   = Path::clean($location . '/');
            $base   = $this->getBasePath();

            if (file_exists(Path::clean($base . $path . $name))) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FOLDER_EXISTS'), 'error');

                return false;
            }

            if (!Folder::create(Path::clean($base . $path . $name))) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FOLDER_CREATE_ERROR'), 'error');

                return false;
            }

            return true;
        }
    }

    /**
     * Delete a folder.
     *
     * @param   string  $location  The name and location of the folder.
     *
     * @return  boolean  True if override folder is deleted successfully, false otherwise
     *
     * @since   3.2
     */
    public function deleteFolder($location)
    {
        if ($this->getTemplate()) {
            $app  = Factory::getApplication();
            $base = $this->getBasePath();
            $path = Path::clean($location . '/');

            if (!file_exists($base . $path)) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FOLDER_NOT_EXISTS'), 'error');

                return false;
            }

            $return = Folder::delete($base . $path);

            if (!$return) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FOLDER_DELETE_ERROR'), 'error');

                return false;
            }

            return true;
        }
    }

    /**
     * Rename a file.
     *
     * @param   string  $file  The name and location of the old file
     * @param   string  $name  The new name of the file.
     *
     * @return  string  Encoded string containing the new file location.
     *
     * @since   3.2
     */
    public function renameFile($file, $name)
    {
        if ($this->getTemplate()) {
            $app          = Factory::getApplication();
            $path         = $this->getBasePath();
            $fileName     = base64_decode($file);
            $explodeArray = explode('.', $fileName);
            $type         = end($explodeArray);
            $explodeArray = explode('/', $fileName);
            $newName      = str_replace(end($explodeArray), $name . '.' . $type, $fileName);

            if (file_exists($path . $newName)) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_EXISTS'), 'error');

                return false;
            }

            if (!rename($path . $fileName, $path . $newName)) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_RENAME_ERROR'), 'error');

                return false;
            }

            return base64_encode($newName);
        }
    }

    /**
     * Get an image address, height and width.
     *
     * @return  array an associative array containing image address, height and width.
     *
     * @since   3.2
     */
    public function getImage()
    {
        if ($this->getTemplate()) {
            $app      = Factory::getApplication();
            $fileName = base64_decode($app->getInput()->get('file'));
            $path     = $this->getBasePath();

            $uri = Uri::root(false) . ltrim(str_replace(JPATH_ROOT, '', $this->getBasePath()), '/');

            if (file_exists(Path::clean($path . $fileName))) {
                $JImage           = new Image(Path::clean($path . $fileName));
                $image['address'] = $uri . $fileName;
                $image['path']    = $fileName;
                $image['height']  = $JImage->getHeight();
                $image['width']   = $JImage->getWidth();
            } else {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_IMAGE_FILE_NOT_FOUND'), 'error');

                return false;
            }

            return $image;
        }
    }

    /**
     * Crop an image.
     *
     * @param   string  $file  The name and location of the file
     * @param   string  $w     width.
     * @param   string  $h     height.
     * @param   string  $x     x-coordinate.
     * @param   string  $y     y-coordinate.
     *
     * @return  boolean     true if image cropped successfully, false otherwise.
     *
     * @since   3.2
     */
    public function cropImage($file, $w, $h, $x, $y)
    {
        if ($this->getTemplate()) {
            $app      = Factory::getApplication();
            $path     = $this->getBasePath() . base64_decode($file);

            try {
                $image      = new Image($path);
                $properties = $image->getImageFileProperties($path);

                switch ($properties->mime) {
                    case 'image/webp':
                        $imageType = \IMAGETYPE_WEBP;
                        break;
                    case 'image/png':
                        $imageType = \IMAGETYPE_PNG;
                        break;
                    case 'image/gif':
                        $imageType = \IMAGETYPE_GIF;
                        break;
                    default:
                        $imageType = \IMAGETYPE_JPEG;
                }

                $image->crop($w, $h, $x, $y, false);
                $image->toFile($path, $imageType);

                return true;
            } catch (\Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }
    }

    /**
     * Resize an image.
     *
     * @param   string  $file    The name and location of the file
     * @param   string  $width   The new width of the image.
     * @param   string  $height  The new height of the image.
     *
     * @return   boolean  true if image resize successful, false otherwise.
     *
     * @since   3.2
     */
    public function resizeImage($file, $width, $height)
    {
        if ($this->getTemplate()) {
            $app  = Factory::getApplication();
            $path = $this->getBasePath() . base64_decode($file);

            try {
                $image      = new Image($path);
                $properties = $image->getImageFileProperties($path);

                switch ($properties->mime) {
                    case 'image/webp':
                        $imageType = \IMAGETYPE_WEBP;
                        break;
                    case 'image/png':
                        $imageType = \IMAGETYPE_PNG;
                        break;
                    case 'image/gif':
                        $imageType = \IMAGETYPE_GIF;
                        break;
                    default:
                        $imageType = \IMAGETYPE_JPEG;
                }

                $image->resize($width, $height, false, Image::SCALE_FILL);
                $image->toFile($path, $imageType);

                return true;
            } catch (\Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }
    }

    /**
     * Template preview.
     *
     * @return  object  object containing the id of the template.
     *
     * @since   3.2
     */
    public function getPreview()
    {
        $app   = Factory::getApplication();
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['id', 'client_id']));
        $query->from($db->quoteName('#__template_styles'));
        $query->where($db->quoteName('template') . ' = :template')
            ->bind(':template', $this->template->element);

        $db->setQuery($query);

        try {
            $result = $db->loadObject();
        } catch (\RuntimeException $e) {
            $app->enqueueMessage($e->getMessage(), 'warning');
        }

        if (empty($result)) {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_EXTENSION_RECORD_NOT_FOUND'), 'warning');
        } else {
            return $result;
        }
    }

    /**
     * Rename a file.
     *
     * @return  mixed  array on success, false on failure
     *
     * @since   3.2
     */
    public function getFont()
    {
        if ($template = $this->getTemplate()) {
            $app          = Factory::getApplication();
            $client       = ApplicationHelper::getClientInfo($template->client_id);
            $relPath      = base64_decode($app->getInput()->get('file'));
            $explodeArray = explode('/', $relPath);
            $fileName     = end($explodeArray);
            $path         = $this->getBasePath() . base64_decode($app->getInput()->get('file'));

            if (stristr($client->path, 'administrator') == false) {
                $folder = '/templates/';
            } else {
                $folder = '/administrator/templates/';
            }

            $uri = Uri::root(true) . $folder . $template->element;

            if (file_exists(Path::clean($path))) {
                $font['address'] = $uri . $relPath;

                $font['rel_path'] = $relPath;

                $font['name'] = $fileName;
            } else {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_FONT_FILE_NOT_FOUND'), 'error');

                return false;
            }

            return $font;
        }
    }

    /**
     * Copy a file.
     *
     * @param   string  $newName   The name of the copied file
     * @param   string  $location  The final location where the file is to be copied
     * @param   string  $file      The name and location of the file
     *
     * @return   boolean  true if image resize successful, false otherwise.
     *
     * @since   3.2
     */
    public function copyFile($newName, $location, $file)
    {
        if ($this->getTemplate()) {
            $app          = Factory::getApplication();
            $relPath      = base64_decode($file);
            $explodeArray = explode('.', $relPath);
            $ext          = end($explodeArray);
            $path         = $this->getBasePath();
            $newPath      = Path::clean($path . $location . '/' . $newName . '.' . $ext);

            if (file_exists($newPath)) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_EXISTS'), 'error');

                return false;
            }

            if (File::copy($path . $relPath, $newPath)) {
                $app->enqueueMessage(Text::sprintf('COM_TEMPLATES_FILE_COPY_SUCCESS', $newName . '.' . $ext));

                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Get the compressed files.
     *
     * @return   array if file exists, false otherwise
     *
     * @since   3.2
     */
    public function getArchive()
    {
        if ($this->getTemplate()) {
            $app  = Factory::getApplication();
            $path = $this->getBasePath() . base64_decode($app->getInput()->get('file'));

            if (file_exists(Path::clean($path))) {
                $files = [];
                $zip   = new \ZipArchive();

                if ($zip->open($path) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entry   = $zip->getNameIndex($i);
                        $files[] = $entry;
                    }
                } else {
                    $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_ARCHIVE_OPEN_FAIL'), 'error');

                    return false;
                }
            } else {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_FONT_FILE_NOT_FOUND'), 'error');

                return false;
            }

            return $files;
        }
    }

    /**
     * Extract contents of an archive file.
     *
     * @param   string  $file  The name and location of the file
     *
     * @return  boolean  true if image extraction is successful, false otherwise.
     *
     * @since   3.2
     */
    public function extractArchive($file)
    {
        if ($this->getTemplate()) {
            $app          = Factory::getApplication();
            $relPath      = base64_decode($file);
            $explodeArray = explode('/', $relPath);
            $fileName     = end($explodeArray);
            $path         = $this->getBasePath() . base64_decode($file);

            if (file_exists(Path::clean($path . '/' . $fileName))) {
                $zip = new \ZipArchive();

                if ($zip->open(Path::clean($path . '/' . $fileName)) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entry = $zip->getNameIndex($i);

                        if (file_exists(Path::clean($path . '/' . $entry))) {
                            $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_ARCHIVE_EXISTS'), 'error');

                            return false;
                        }
                    }

                    $zip->extractTo($path);

                    return true;
                } else {
                    $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_ARCHIVE_OPEN_FAIL'), 'error');

                    return false;
                }
            } else {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_FILE_ARCHIVE_NOT_FOUND'), 'error');

                return false;
            }
        }
    }

    /**
     * Check if the extension is allowed and will be shown in the template manager
     *
     * @param   string  $ext  The extension to check if it is allowed
     *
     * @return  boolean  true if the extension is allowed false otherwise
     *
     * @since   3.6.0
     */
    protected function checkFormat($ext)
    {
        if (!isset($this->allowedFormats)) {
            $params       = ComponentHelper::getParams('com_templates');
            $imageTypes   = explode(',', $params->get('image_formats', 'gif,bmp,jpg,jpeg,png,webp'));
            $sourceTypes  = explode(',', $params->get('source_formats', 'txt,less,ini,xml,js,php,css,scss,sass,json'));
            $fontTypes    = explode(',', $params->get('font_formats', 'woff,woff2,ttf,otf'));
            $archiveTypes = explode(',', $params->get('compressed_formats', 'zip'));

            $this->allowedFormats = array_merge($imageTypes, $sourceTypes, $fontTypes, $archiveTypes);
            $this->allowedFormats = array_map('strtolower', $this->allowedFormats);
        }

        return in_array(strtolower($ext), $this->allowedFormats);
    }

    /**
     * Method to get a list of all the files to edit in a template's media folder.
     *
     * @return  array  A nested array of relevant files.
     *
     * @since   4.1.0
     */
    public function getMediaFiles()
    {
        $result   = [];
        $template = $this->getTemplate();

        if (!isset($template->xmldata)) {
            $template->xmldata = TemplatesHelper::parseXMLTemplateFile($template->client_id === 0 ? JPATH_ROOT : JPATH_ROOT . '/administrator', $template->name);
        }

        if (!isset($template->xmldata->inheritable) || (isset($template->xmldata->parent) && $template->xmldata->parent === '')) {
            return $result;
        }

        $app                = Factory::getApplication();
        $path               = Path::clean(JPATH_ROOT . '/media/templates/' . ($template->client_id === 0 ? 'site' : 'administrator') . '/' . $template->element . '/');
        $this->mediaElement = $path;

        if (!is_writable($path)) {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_DIRECTORY_NOT_WRITABLE'), 'error');
        }

        if (is_dir($path)) {
            $result = $this->getDirectoryTree($path);
        }

        return $result;
    }

    /**
     * Method to resolve the base folder.
     *
     * @return  string  The absolute path for the base.
     *
     * @since   4.1.0
     */
    private function getBasePath()
    {
        $app      = Factory::getApplication();
        $isMedia  = $app->getInput()->getInt('isMedia', 0);

        return $isMedia ? JPATH_ROOT . '/media/templates/' . ($this->template->client_id === 0 ? 'site' : 'administrator') . '/' . $this->template->element :
            JPATH_ROOT . '/' . ($this->template->client_id === 0 ? '' : 'administrator/') . 'templates/' . $this->template->element;
    }

    /**
     * Method to create the templateDetails.xml for the child template
     *
     * @return  boolean   true if name is not used, false otherwise
     *
     * @since  4.1.0
     */
    public function child()
    {
        $app      = Factory::getApplication();
        $template = $this->getTemplate();

        if (!(array) $template) {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_WRITE'), 'error');

            return false;
        }

        $client   = ApplicationHelper::getClientInfo($template->client_id);
        $fromPath = Path::clean($client->path . '/templates/' . $template->element . '/templateDetails.xml');

        // Delete new folder if it exists
        $toPath = $this->getState('to_path');

        if (Folder::exists($toPath)) {
            if (!Folder::delete($toPath)) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_WRITE'), 'error');

                return false;
            }
        } else {
            if (!Folder::create($toPath)) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_WRITE'), 'error');

                return false;
            }
        }

        // Create the html folder
        Folder::create($toPath . '/html');

        // Copy the template definition from the parent template
        if (!File::copy($fromPath, $toPath . '/templateDetails.xml')) {
            return false;
        }

        // Check manifest for additional files
        $newName  = strtolower($this->getState('new_name'));
        $template = $this->getTemplate();

        // Edit XML file
        $xmlFile = Path::clean($this->getState('to_path') . '/templateDetails.xml');

        if (!File::exists($xmlFile)) {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'), 'error');

            return false;
        }

        try {
            $xml = simplexml_load_string(file_get_contents($xmlFile));
        } catch (\Exception $e) {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_READ'), 'error');

            return false;
        }

        $user = $this->getCurrentUser();
        unset($xml->languages);
        unset($xml->media);
        unset($xml->files);
        unset($xml->parent);
        unset($xml->inheritable);

        // Remove the update parts
        unset($xml->update);
        unset($xml->updateservers);

        if (isset($xml->creationDate)) {
            $xml->creationDate = (new Date('now'))->format('F Y');
        } else {
            $xml->addChild('creationDate', (new Date('now'))->format('F Y'));
        }

        if (isset($xml->author)) {
            $xml->author = $user->name;
        } else {
            $xml->addChild('author', $user->name);
        }

        if (isset($xml->authorEmail)) {
            $xml->authorEmail = $user->email;
        } else {
            $xml->addChild('authorEmail', $user->email);
        }

        $files = $xml->addChild('files');
        $files->addChild('filename', 'templateDetails.xml');
        $files->addChild('folder', 'html');

        // Media folder
        $media = $xml->addChild('media');
        $media->addAttribute('folder', 'media');
        $media->addAttribute('destination', 'templates/' . ($template->client_id === 0 ? 'site/' : 'administrator/') . $template->element . '_' . $newName);
        $media->addChild('folder', 'css');
        $media->addChild('folder', 'js');
        $media->addChild('folder', 'images');
        $media->addChild('folder', 'scss');

        $xml->name = $template->element . '_' . $newName;

        if (isset($xml->namespace)) {
            $xml->namespace = $xml->namespace . '_' . ucfirst($newName);
        }

        $xml->inheritable = 0;
        $files            = $xml->addChild('parent', $template->element);

        $dom                     = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($xml->asXML());

        $result = File::write($xmlFile, $dom->saveXML());

        if (!$result) {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_WRITE'), 'error');

            return false;
        }

        // Create an empty media folder structure
        if (
            !Folder::create($toPath . '/media')
            || !Folder::create($toPath . '/media/css')
            || !Folder::create($toPath . '/media/js')
            || !Folder::create($toPath . '/media/images')
            || !Folder::create($toPath . '/media/scss')
        ) {
            return false;
        }

        return true;
    }

    /**
     * Method to get the parent template existing styles
     *
     * @return  array   array of id,titles of the styles
     *
     * @since  4.1.3
     */
    public function getAllTemplateStyles()
    {
        $template = $this->getTemplate();

        if (empty($template->xmldata->inheritable)) {
            return [];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['id', 'title']))
            ->from($db->quoteName('#__template_styles'))
            ->where($db->quoteName('client_id') . ' = :client_id', 'AND')
            ->where($db->quoteName('template') . ' = :template')
            ->orWhere($db->quoteName('parent') . ' = :parent')
            ->bind(':client_id', $template->client_id, ParameterType::INTEGER)
            ->bind(':template', $template->element)
            ->bind(':parent', $template->element);

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Method to copy selected styles to the child template
     *
     * @return  boolean   true if name is not used, false otherwise
     *
     * @since  4.1.3
     */
    public function copyStyles()
    {
        $app         = Factory::getApplication();
        $template    = $this->getTemplate();
        $newName     = strtolower($this->getState('new_name'));
        $applyStyles = $this->getState('stylesToCopy');

        // Get a db connection.
        $db = $this->getDatabase();

        // Create a new query object.
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['title', 'params']))
            ->from($db->quoteName('#__template_styles'))
            ->whereIn($db->quoteName('id'), ArrayHelper::toInteger($applyStyles));
        // Reset the query using our newly populated query object.
        $db->setQuery($query);

        try {
            $parentStyle = $db->loadObjectList();
        } catch (\Exception $e) {
            $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_STYLE_NOT_FOUND'), 'error');

            return false;
        }

        foreach ($parentStyle as $style) {
            $query     = $db->getQuery(true);
            $styleName = Text::sprintf('COM_TEMPLATES_COPY_CHILD_TEMPLATE_STYLES', ucfirst($template->element . '_' . $newName), $style->title);

            // Insert columns and values
            $columns = ['id', 'template', 'client_id', 'home', 'title', 'inheritable', 'parent', 'params'];
            $values  = [0, $db->quote($template->element . '_' . $newName), (int) $template->client_id, $db->quote('0'), $db->quote($styleName), 0, $db->quote($template->element), $db->quote($style->params)];

            $query
                ->insert($db->quoteName('#__template_styles'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\Exception $e) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_READ'), 'error');

                return false;
            }
        }

        return true;
    }
}
