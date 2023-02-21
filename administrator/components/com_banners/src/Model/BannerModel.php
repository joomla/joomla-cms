<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Banner model.
 *
 * @since  1.6
 */
class BannerModel extends AdminModel
{
    use VersionableModelTrait;

    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix = 'COM_BANNERS_BANNER';

    /**
     * The type alias for this content type.
     *
     * @var    string
     * @since  3.2
     */
    public $typeAlias = 'com_banners.banner';

    /**
     * Batch copy/move command. If set to false, the batch copy/move command is not supported
     *
     * @var  string
     */
    protected $batch_copymove = 'category_id';

    /**
     * Allowed batch commands
     *
     * @var  array
     */
    protected $batch_commands = [
        'client_id'   => 'batchClient',
        'language_id' => 'batchLanguage',
    ];

    /**
     * Batch client changes for a group of banners.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since   2.5
     */
    protected function batchClient($value, $pks, $contexts)
    {
        // Set the variables
        $user = $this->getCurrentUser();

        /** @var \Joomla\Component\Banners\Administrator\Table\BannerTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if (!$user->authorise('core.edit', $contexts[$pk])) {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }

            $table->reset();
            $table->load($pk);
            $table->cid = (int) $value;

            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->state != -2) {
            return false;
        }

        if (!empty($record->catid)) {
            return $this->getCurrentUser()->authorise('core.delete', 'com_banners.category.' . (int) $record->catid);
        }

        return parent::canDelete($record);
    }

    /**
     * A method to preprocess generating a new title in order to allow tables with alternative names
     * for alias and title to use the batch move and copy methods
     *
     * @param   integer  $categoryId  The target category id
     * @param   Table    $table       The JTable within which move or copy is taking place
     *
     * @return  void
     *
     * @since   3.8.12
     */
    public function generateTitle($categoryId, $table)
    {
        // Alter the title & alias
        $data         = $this->generateNewTitle($categoryId, $table->alias, $table->name);
        $table->name  = $data['0'];
        $table->alias = $data['1'];
    }

    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        // Check against the category.
        if (!empty($record->catid)) {
            return $this->getCurrentUser()->authorise('core.edit.state', 'com_banners.category.' . (int) $record->catid);
        }

        // Default to component settings if category not known.
        return parent::canEditState($record);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form. [optional]
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
     *
     * @return  Form|boolean  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_banners.banner', 'banner', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object) $data)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('sticky', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('sticky', 'filter', 'unset');
        }

        // Don't allow to change the created_by user if not allowed to access com_users.
        if (!$this->getCurrentUser()->authorise('core.manage', 'com_users')) {
            $form->setFieldAttribute('created_by', 'filter', 'unset');
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
        // Check the session for previously entered form data.
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_banners.edit.banner.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            // Prime some default values.
            if ($this->getState('banner.id') == 0) {
                $filters     = (array) $app->getUserState('com_banners.banners.filter');
                $filterCatId = $filters['category_id'] ?? null;

                $data->set('catid', $app->getInput()->getInt('catid', $filterCatId));
            }
        }

        $this->preprocessData('com_banners.banner', $data);

        return $data;
    }

    /**
     * Method to stick records.
     *
     * @param   array    $pks    The ids of the items to publish.
     * @param   integer  $value  The value of the published state
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function stick(&$pks, $value = 1)
    {
        /** @var \Joomla\Component\Banners\Administrator\Table\BannerTable $table */
        $table = $this->getTable();
        $pks   = (array) $pks;

        // Access checks.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if (!$this->canEditState($table)) {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
                }
            }
        }

        // Attempt to change the state of the records.
        if (!$table->stick($pks, $value, $this->getCurrentUser()->id)) {
            $this->setError($table->getError());

            return false;
        }

        return true;
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   Table  $table  A record object.
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since   1.6
     */
    protected function getReorderConditions($table)
    {
        $db = $this->getDatabase();

        return [
            $db->quoteName('catid') . ' = ' . (int) $table->catid,
            $db->quoteName('state') . ' >= 0',
        ];
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   Table  $table  A Table object.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareTable($table)
    {
        $date = Factory::getDate();
        $user = $this->getCurrentUser();

        if (empty($table->id)) {
            // Set the values
            $table->created    = $date->toSql();
            $table->created_by = $user->id;

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select('MAX(' . $db->quoteName('ordering') . ')')
                    ->from($db->quoteName('#__banners'));

                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            // Set the values
            $table->modified    = $date->toSql();
            $table->modified_by = $user->id;
        }

        // Increment the content version number.
        $table->version++;
    }

    /**
     * Allows preprocessing of the Form object.
     *
     * @param   Form    $form   The form object
     * @param   array   $data   The data to be merged into the form object
     * @param   string  $group  The plugin group to be executed
     *
     * @return  void
     *
     * @since    3.6.1
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        if ($this->canCreateCategory()) {
            $form->setFieldAttribute('catid', 'allowAdd', 'true');

            // Add a prefix for categories created on the fly.
            $form->setFieldAttribute('catid', 'customPrefix', '#new#');
        }

        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data)
    {
        $input = Factory::getApplication()->getInput();

        // Create new category, if needed.
        $createCategory = true;

        // If category ID is provided, check if it's valid.
        if (is_numeric($data['catid']) && $data['catid']) {
            $createCategory = !CategoriesHelper::validateCategoryId($data['catid'], 'com_banners');
        }

        // Save New Category
        if ($createCategory && $this->canCreateCategory()) {
            $category = [
                // Remove #new# prefix, if exists.
                'title'     => strpos($data['catid'], '#new#') === 0 ? substr($data['catid'], 5) : $data['catid'],
                'parent_id' => 1,
                'extension' => 'com_banners',
                'language'  => $data['language'],
                'published' => 1,
            ];

            /** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $categoryModel */
            $categoryModel = Factory::getApplication()->bootComponent('com_categories')
                ->getMVCFactory()->createModel('Category', 'Administrator', ['ignore_request' => true]);

            // Create new category.
            if (!$categoryModel->save($category)) {
                $this->setError($categoryModel->getError());

                return false;
            }

            // Get the new category ID.
            $data['catid'] = $categoryModel->getState('category.id');
        }

        // Alter the name for save as copy
        if ($input->get('task') == 'save2copy') {
            /** @var \Joomla\Component\Banners\Administrator\Table\BannerTable $origTable */
            $origTable = clone $this->getTable();
            $origTable->load($input->getInt('id'));

            if ($data['name'] == $origTable->name) {
                list($name, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['name']);
                $data['name']       = $name;
                $data['alias']      = $alias;
            } else {
                if ($data['alias'] == $origTable->alias) {
                    $data['alias'] = '';
                }
            }

            $data['state'] = 0;
        }

        return parent::save($data);
    }

    /**
     * Is the user allowed to create an on the fly category?
     *
     * @return  boolean
     *
     * @since   3.6.1
     */
    private function canCreateCategory()
    {
        return $this->getCurrentUser()->authorise('core.create', 'com_banners');
    }
}
