<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tags Component Tag Model
 *
 * @since  3.1
 */
class TagModel extends AdminModel
{
    use VersionableModelTrait;

    /**
     * @var    string  The prefix to use with controller messages.
     * @since  3.1
     */
    protected $text_prefix = 'COM_TAGS';

    /**
     * @var    string  The type alias for this content type.
     * @since  3.2
     */
    public $typeAlias = 'com_tags.tag';

    /**
     * The context used for the associations table
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $associationsContext = 'com_tags.tag';

    /**
     * Allowed batch commands
     *
     * @var    array
     * @since  3.7.0
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage',
    ];

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   3.1
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        return parent::canDelete($record);
    }

    /**
     * Auto-populate the model state.
     *
     * @note Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   3.1
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        $parentId = $app->getInput()->getInt('parent_id');
        $this->setState('tag.parent_id', $parentId);

        // Load the User state.
        $pk = $app->getInput()->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_tags');
        $this->setState('params', $params);
    }

    /**
     * Method to get a tag.
     *
     * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
     *
     * @return  mixed  Tag data object on success, false on failure.
     *
     * @since   3.1
     */
    public function getItem($pk = null)
    {
        if ($result = parent::getItem($pk)) {
            // Prime required properties.
            if (empty($result->id)) {
                $result->parent_id = $this->getState('tag.parent_id');
            }

            // Convert the metadata field to an array.
            $registry         = new Registry($result->metadata);
            $result->metadata = $registry->toArray();

            // Convert the images field to an array.
            $registry       = new Registry($result->images);
            $result->images = $registry->toArray();

            // Convert the urls field to an array.
            $registry     = new Registry($result->urls);
            $result->urls = $registry->toArray();

            // Convert the modified date to local user time for display in the form.
            $tz = new \DateTimeZone(Factory::getApplication()->get('offset'));

            if ((int) $result->modified_time) {
                $date = new Date($result->modified_time);
                $date->setTimezone($tz);
                $result->modified_time = $date->toSql(true);
            } else {
                $result->modified_time = null;
            }
        }


        // Load associated content items
        $assoc = Associations::isEnabled();

        if ($assoc) {
            $result->associations = array();

            if ($result->id != null) {
                $associations = Associations::getAssociations('com_tags', '#__tags', 'com_tags.tag', $result->id, 'id', 'alias', null);

                foreach ($associations as $tag => $association) {
                    $result->associations[$tag] = $association->id;
                }
            }
        }

        return $result;
    }

    /**
     * Method to get the row form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  bool|\Joomla\CMS\Form\Form  A Form object on success, false on failure
     *
     * @since   3.1
     */
    public function getForm($data = [], $loadData = true)
    {
        $jinput = Factory::getApplication()->getInput();

        // Get the form.
        $form = $this->loadForm('com_tags.tag', 'tag', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->authorise('core.edit.state', 'com_tags' . $jinput->get('id'))) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   3.1
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_tags.edit.tag.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_tags.tag', $data);

        return $data;
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
     * @since   __DEPLOY_VERSION__
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        // Association content items
        if (Associations::isEnabled()) {
            $languages = LanguageHelper::getContentLanguages(false, false, null, 'ordering', 'asc');

            if (count($languages) > 1) {
                $addform = new \SimpleXMLElement('<form />');
                $fields = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language) {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'tag');
                    $field->addAttribute('language', $language->lang_code);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $field->addAttribute('select', 'true');
                    $field->addAttribute('new', 'true');
                    $field->addAttribute('edit', 'true');
                    $field->addAttribute('clear', 'true');
                    $field->addAttribute('propagate', 'true');
                }

                $form->load($addform, false);
            }
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
     * @since   3.1
     */
    public function save($data)
    {
        $return = parent::save($data);

        if ($return) {
            /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */
            $table = $this->getTable();

            // Rebuild the path for the tag:
            if (!$table->rebuildPath($this->getState($this->getName() . '.id'))) {
                $this->setError($table->getError());

                return false;
            }

            // Rebuild the paths of the tag's children:
            if (!$table->rebuild($this->getState($this->getName() . '.id'))) {
                $this->setError($table->getError());

                return false;
            }
        }

        return $return;
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param   \Joomla\CMS\Table\Table  $table  A Table object.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareTable($table)
    {
        // Increment the content version number.
        $table->version++;
    }

    /**
     * Method rebuild the entire nested set tree.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   3.1
     */
    public function rebuild()
    {
        // Get an instance of the table object.
        /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */

        $table = $this->getTable();

        if (!$table->rebuild()) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to save the reordered nested set tree.
     * First we save the new order values in the lft values of the changed ids.
     * Then we invoke the table rebuild to implement the new ordering.
     *
     * @param   array    $idArray   An array of primary key ids.
     * @param   integer  $lftArray  The lft value
     *
     * @return  boolean  False on failure or error, True otherwise
     *
     * @since   3.1
     */
    public function saveorder($idArray = null, $lftArray = null)
    {
        // Get an instance of the table object.
        /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */

        $table = $this->getTable();

        if (!$table->saveorder($idArray, $lftArray)) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to change the title & alias.
     *
     * @param   integer  $parentId  The id of the parent.
     * @param   string   $alias     The alias.
     * @param   string   $title     The title.
     *
     * @return  array  Contains the modified title and alias.
     *
     * @since   3.1
     */
    protected function generateNewTitle($parentId, $alias, $title)
    {
        // Alter the title & alias
        /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */

        $table = $this->getTable();

        while ($table->load(['alias' => $alias, 'parent_id' => $parentId])) {
            $title = ($table->title != $title) ? $title : StringHelper::increment($title);
            $alias = StringHelper::increment($alias, 'dash');
        }

        return [$title, $alias];
    }
}
