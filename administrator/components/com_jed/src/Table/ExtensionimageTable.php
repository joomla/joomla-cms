<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Table;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table as Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Extensionimage table
 *
 * @since 4.0.0
 */
class ExtensionimageTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  A database connector object
     *
     * @since 4.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_jed.extensionimage';
        parent::__construct('#__jed_extension_images', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    /**
     * This function convert an array of Access objects into an rules array.
     *
     * @param   array  $jaccessrules  An array of Access objects.
     *
     * @return  array
     *
     * @since 4.0.0
     */
    private function JAccessRulestoArray(array $jaccessrules): array
    {
        $rules = [];

        foreach ($jaccessrules as $action => $jaccess) {
            $actions = [];

            if ($jaccess) {
                foreach ($jaccess->getData() as $group => $allow) {
                    $actions[$group] = ((bool) $allow);
                }
            }

            $rules[$action] = $actions;
        }

        return $rules;
    }

    /**
     * Define a namespaced asset name for inclusion in the #__assets table
     *
     * @return string The asset name
     *
     * @see   Table::_getAssetName
     *
     * @since 4.0.0
     */
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return $this->typeAlias . '.' . (int) $this->$k;
    }

    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param   array  $src     Named array
     * @param   mixed  $ignore  Optional array or list of parameters to ignore
     *
     * @return  null|string  null is operation was satisfactory, otherwise returns an error
     *
     * @see     Table:bind
     * @throws Exception
     * @since   4.0.0
     */
    public function bind($src, $ignore = ''): ?string
    {
        $task = Factory::getApplication()->input->get('task');

        $input = Factory::getApplication()->input;
        $task  = $input->getString('task', '');

        if ($src['id'] == 0 && empty($src['created_by'])) {
            $src['created_by'] = JedHelper::getUser()->id;
        }

        if ($src['id'] == 0 && empty($src['modified_by'])) {
            $src['modified_by'] = JedHelper::getUser()->id;
        }

        if ($task == 'apply' || $task == 'save') {
            $src['modified_by'] = JedHelper::getUser()->id;
        }

        // Support for multiple or not foreign key field: extension_id
        if (!empty($src['extension_id'])) {
            if (is_array($src['extension_id'])) {
                $src['extension_id'] = implode(',', $src['extension_id']);
            } elseif (strrpos($src['extension_id'], ',') != false) {
                $src['extension_id'] = explode(',', $src['extension_id']);
            }
        } else {
            $src['extension_id'] = 0;
        }
        // Support for multi file field: filename
        if (!empty($src['filename'])) {
            if (is_array($src['filename'])) {
                $src['filename'] = implode(',', $src['filename']);
            } elseif (strpos($src['filename'], ',') != false) {
                $src['filename'] = explode(',', $src['filename']);
            }
        } else {
            $src['filename'] = '';
        }


        if (isset($src['params']) && is_array($src['params'])) {
            $registry = new Registry();
            $registry->loadArray($src['params']);
            $src['params'] = (string) $registry;
        }

        if (isset($src['metadata']) && is_array($src['metadata'])) {
            $registry = new Registry();
            $registry->loadArray($src['metadata']);
            $src['metadata'] = (string) $registry;
        }

        if (!JedHelper::getUser()->authorise('core.admin', 'com_jed.extensionimage.' . $src['id'])) {
            $actions         = Access::getActionsFromFile(
                JPATH_ADMINISTRATOR . '/components/com_jed/access.xml',
                "/access/section[@name='extensionimage']/"
            );
            $default_actions = Access::getAssetRules('com_jed.extensionimage.' . $src['id'])->getData();
            $array_jaccess   = [];

            foreach ($actions as $action) {
                if (key_exists($action->name, $default_actions)) {
                    $array_jaccess[$action->name] = $default_actions[$action->name];
                }
            }

            $src['rules'] = $this->JAccessRulestoArray($array_jaccess);
        }

        // Bind the rules for ACL where supported.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $this->setRules($array['rules']);
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Overloaded check function
     *
     * @return bool
     *
     * @since 4.0.0
     * @throws Exception
     * @throws Exception
     */
    public function check(): bool
    {
        // If there is an ordering column and this is a new row then get the next ordering value
        if (property_exists($this, 'ordering') && $this->get('id') == 0) {
            $this->ordering = self::getNextOrder();
        }


        // Support multi file field: filename
        $app   = Factory::getApplication();
        $files = $app->input->files->get('jform', [], 'raw');
        $array = $app->input->get('jform', [], 'ARRAY');

        if ($files['filename'][0]['size'] > 0) {
            // Deleting existing files
            $oldFiles = JedHelper::getFiles($this->id, $this->_tbl, 'filename');

            foreach ($oldFiles as $f) {
                $oldFile = JPATH_ROOT . '//tmp/' . $f;

                if (file_exists($oldFile) && !is_dir($oldFile)) {
                    unlink($oldFile);
                }
            }

            $this->filename = "";

            foreach ($files['filename'] as $singleFile) {
                // Check if the server found any error.
                $fileError = $singleFile['error'];
                $message   = '';

                if ($fileError > 0 && $fileError != 4) {
                    switch ($fileError) {
                        case 1:
                            $message = Text::_('File size exceeds allowed by the server');
                            break;
                        case 2:
                            $message = Text::_('File size exceeds allowed by the html form');
                            break;
                        case 3:
                            $message = Text::_('Partial upload error');
                            break;
                    }

                    if ($message != '') {
                        $app->enqueueMessage($message, 'warning');

                        return false;
                    }
                } elseif ($fileError == 4) {
                    if (isset($array['filename'])) {
                        $this->set('filename', $array['filename']);
                    }
                } else {
                    // Replace any special characters in the filename
                    $filename   = File::stripExt($singleFile['name']);
                    $extension  = File::getExt($singleFile['name']);
                    $filename   = preg_replace("/[^A-Za-z0-9]/i", "-", $filename);
                    $filename   = $filename . '.' . $extension;
                    $uploadPath = JPATH_ROOT . '//tmp/' . $filename;
                    $fileTemp   = $singleFile['tmp_name'];

                    if (!File::exists($uploadPath)) {
                        if (!File::upload($fileTemp, $uploadPath)) {
                            $app->enqueueMessage('Error moving file', 'warning');

                            return false;
                        }
                    }
                    $lfname = $this->get('filename');
                    $this->set('filename', $lfname .= (!empty($lfname)) ? "," : "");
                    $lfname = $this->get('filename');
                    $this->set('filename', $lfname .= $filename);
                }
            }
        } else {
            $lfname = $this->get('filename');
            $this->set('filename', $lfname .= $array['filename_hidden']);
        }

        return parent::check();
    }

    /**
     * Delete a record by id
     *
     * @param   mixed  $pk  Primary key value to delete. Optional
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public function delete($pk = null): bool
    {
        $this->load($pk);
        $result = parent::delete($pk);

        if ($result) {
            $checkImageVariableType = gettype($this->get('filename'));

            switch ($checkImageVariableType) {
                case 'string':
                    File::delete(JPATH_ROOT . '/tmp/' . $this->filename);
                    break;
                default:
                    foreach ($this->filename as $filenameFile) {
                        File::delete(JPATH_ROOT . '/tmp/' . $filenameFile);
                    }
            }
        }

        return $result;
    }

    /**
     * Get the type alias for the history table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias(): string
    {
        return $this->typeAlias;
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     *
     * If a primary key value is set the row with that primary key value will be updated with the instance property values.
     * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   4.0.0
     */
    public function store($updateNulls = true): bool
    {
        return parent::store($updateNulls);
    }
}
