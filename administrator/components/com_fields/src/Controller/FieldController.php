<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Field controller
 *
 * @since  3.7.0
 */
class FieldController extends FormController
{
    /**
     * @var    string
     */
    private $internalContext;

    /**
     * @var    string
     */
    private $component;

    /**
     * The prefix to use with controller messages.
     *
     * @var    string

     * @since   3.7.0
     */
    protected $text_prefix = 'COM_FIELDS_FIELD';

    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     *                                          Recognized key values include 'name', 'default_task', 'model_path', and
     *                                          'view_path' (this list is not meant to be comprehensive).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    Input
     *
     * @since   3.7.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->internalContext = $this->app->getUserStateFromRequest('com_fields.fields.context', 'context', 'com_content.article', 'CMD');
        $parts                 = FieldsHelper::extract($this->internalContext);
        $this->component       = $parts ? $parts[0] : null;
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    protected function allowAdd($data = [])
    {
        return $this->app->getIdentity()->authorise('core.create', $this->component);
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;
        $user     = $this->app->getIdentity();

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Check edit on the record asset (explicit or inherited)
        if ($user->authorise('core.edit', $this->component . '.field.' . $recordId)) {
            return true;
        }

        // Check edit own on the record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', $this->component . '.field.' . $recordId)) {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            // Grant if current user is owner of the record
            return $user->id == $record->created_user_id;
        }

        return false;
    }

    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean   True if successful, false otherwise and internal error is set.
     *
     * @since   3.7.0
     */
    public function batch($model = null)
    {
        $this->checkToken();

        // Set the model
        $model = $this->getModel('Field');

        // Preset the redirect
        $this->setRedirect('index.php?option=com_fields&view=fields&context=' . $this->internalContext);

        return parent::batch($model);
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   integer  $recordId  The primary key id for the item.
     * @param   string   $urlVar    The name of the URL variable for the id.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   3.7.0
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        return parent::getRedirectToItemAppend($recordId) . '&context=' . $this->internalContext;
    }

    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   3.7.0
     */
    protected function getRedirectToListAppend()
    {
        return parent::getRedirectToListAppend() . '&context=' . $this->internalContext;
    }

    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
        $item = $model->getItem();

        if (isset($item->params) && \is_array($item->params)) {
            $registry = new Registry();
            $registry->loadArray($item->params);
            $item->params = (string) $registry;
        }
    }
}
