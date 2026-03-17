<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Controller;

use Doctrine\Inflector\InflectorFactory;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Model;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryAwareInterface;
use Joomla\CMS\Form\FormFactoryAwareTrait;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller tailored to suit most form-based admin operations.
 *
 * @since  1.6
 * @todo   Add ability to set redirect manually to better cope with frontend usage.
 */
class FormController extends BaseController implements FormFactoryAwareInterface
{
    use FormFactoryAwareTrait;

    /**
     * The Application. Redeclared to show this class requires a web application.
     *
     * @var    CMSWebApplicationInterface
     * @since  5.0.0
     */
    protected $app;

    /**
     * The context for storing internal data, e.g. record.
     *
     * @var    string
     * @since  1.6
     */
    protected $context;

    /**
     * The URL option for the component.
     *
     * @var    string
     * @since  1.6
     */
    protected $option;

    /**
     * The URL view item variable.
     *
     * @var    string
     * @since  1.6
     */
    protected $view_item;

    /**
     * The URL view list variable.
     *
     * @var    string
     * @since  1.6
     */
    protected $view_list;

    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix;

    /**
     * Constructor.
     *
     * @param   array                        $config       An optional associative array of configuration settings.
     *                                                     Recognized key values include 'name', 'default_task',
     *                                                     'model_path', and 'view_path' (this list is not meant to be
     *                                                     comprehensive).
     * @param   ?MVCFactoryInterface         $factory      The factory.
     * @param   ?CMSWebApplicationInterface  $app          The Application for the dispatcher
     * @param   ?Input                       $input        Input
     * @param   ?FormFactoryInterface        $formFactory  The form factory.
     *
     * @since   3.0
     */
    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSWebApplicationInterface $app = null,
        ?Input $input = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        parent::__construct($config, $factory, $app, $input);

        // Guess the option as com_NameOfController
        if (empty($this->option)) {
            $this->option = ComponentHelper::getComponentName($this, $this->getName());
        }

        // Guess the \Text message prefix. Defaults to the option.
        if (empty($this->text_prefix)) {
            $this->text_prefix = strtoupper($this->option);
        }

        // Guess the context as the suffix, eg: OptionControllerContent.
        if (empty($this->context)) {
            $r = null;

            $match = 'Controller';

            // If there is a namespace append a backslash
            if (strpos(\get_class($this), '\\')) {
                $match .= '\\\\';
            }

            if (!preg_match('/(.*)' . $match . '(.*)/i', \get_class($this), $r)) {
                throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_GET_NAME', __METHOD__), 500);
            }

            // Remove the backslashes and the suffix controller
            $this->context = str_replace(['\\', 'controller'], '', strtolower($r[2]));
        }

        // Guess the item view as the context.
        if (empty($this->view_item)) {
            $this->view_item = $this->context;
        }

        // Guess the list view as the plural of the item view.
        if (empty($this->view_list)) {
            $this->view_list = InflectorFactory::create()->build()->pluralize($this->view_item);
        }

        $this->setFormFactory($formFactory);

        // Apply, Save & New, and Save As copy should be standard on forms.
        $this->registerTask('apply', 'save');
        $this->registerTask('save2menu', 'save');
        $this->registerTask('save2new', 'save');
        $this->registerTask('save2copy', 'save');
        $this->registerTask('editAssociations', 'save');
    }

    /**
     * Method to add a new record.
     *
     * @return  boolean  True if the record can be added, false if not.
     *
     * @since   1.6
     */
    public function add()
    {
        $context = "$this->option.edit.$this->context";

        // Access check.
        if (!$this->allowAdd()) {
            // Set the internal error and also the redirect error.
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error');

            $this->setRedirect($this->getRedirectUrlToList());

            return false;
        }

        // Clear the record edit information from the session.
        $this->app->setUserState($context . '.data', null);

        // Redirect to the edit screen.
        $this->setRedirect($this->getRedirectUrlToItem());

        return true;
    }

    /**
     * Method to check if you can add a new record.
     *
     * Extended classes can override this if necessary.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowAdd($data = [])
    {
        $user = $this->app->getIdentity();

        return $user->authorise('core.create', $this->option) || \count($user->getAuthorisedCategories($this->option, 'core.create'));
    }

    /**
     * Method to check if you can edit an existing record.
     *
     * Extended classes can override this if necessary.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        return $this->app->getIdentity()->authorise('core.edit', $this->option);
    }

    /**
     * Method to check if you can save a new or existing record.
     *
     * Extended classes can override this if necessary.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowSave($data, $key = 'id')
    {
        $recordId = $data[$key] ?? '0';

        if ($recordId) {
            return $this->allowEdit($data, $key);
        }

        return $this->allowAdd($data);
    }

    /**
     * Method to run batch operations.
     *
     * @param   BaseDatabaseModel  $model  The model of the component being processed.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since   1.7
     */
    public function batch($model)
    {
        $vars = $this->input->post->get('batch', [], 'array');
        $cid  = (array) $this->input->post->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        // Build an array of item contexts to check
        $contexts = [];

        $option = $this->extension ?? $this->option;

        foreach ($cid as $id) {
            // If we're coming from com_categories, we need to use extension vs. option
            $contexts[$id] = $option . '.' . $this->context . '.' . $id;
        }

        // Attempt to run the batch operation.
        if ($model->batch($vars, $cid, $contexts)) {
            $this->setMessage(Text::_('JLIB_APPLICATION_SUCCESS_BATCH'));

            return true;
        }

        $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_FAILED', $model->getError()), 'warning');

        return false;
    }

    /**
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     *
     * @since   1.6
     */
    public function cancel($key = null)
    {
        $this->checkToken();

        $model   = $this->getModel();
        $table   = $model->getTable();
        $context = "$this->option.edit.$this->context";

        if (empty($key)) {
            $key = $table->getKeyName();
        }

        $recordId = $this->input->getInt($key);
        $checkin  = $table->hasField('checked_out') && $table->hasField('checked_out_time');

        // Attempt to check-in the current record. If not success, go back to the record and display a notice
        if ($recordId && $checkin && !$this->attemptCheckin($model, $recordId, $key)) {
            return false;
        }

        // Clean the session data and redirect.
        $this->releaseEditId($context, $recordId);
        $this->app->setUserState($context . '.data', null);

        // Check if there is a return value
        $return = $this->input->get('return', null, 'base64');

        if (!\is_null($return) && Uri::isInternal(base64_decode($return))) {
            // If a return param exists and is internal, redirect to it.
            $this->setRedirect(Route::_(base64_decode($return), false));
        } else {
            // Otherwise use the standard list URL helper.
            $this->setRedirect($this->getRedirectUrlToList());
        }

        return true;
    }

    /**
     * Method to edit an existing record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key
     *                           (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if access level check and checkout passes, false otherwise.
     *
     * @since   1.6
     */
    public function edit($key = null, $urlVar = null)
    {
        // Do not cache the response to this, its a redirect, and mod_expires and google chrome browser bugs cache it forever!
        $this->app->allowCache(false);

        $model   = $this->getModel();
        $table   = $model->getTable();
        $cid     = (array) $this->input->post->get('cid', [], 'int');
        $context = "$this->option.edit.$this->context";

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        // Get the previous record id (if any) and the current record id.
        $recordId = (int) (\count($cid) ? $cid[0] : $this->input->getInt($urlVar));

        // Access check.
        if (!$this->allowEdit([$key => $recordId], $key)) {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');

            $this->setRedirect($this->getRedirectUrlToList());

            return false;
        }

        $checkin = $table->hasField('checked_out') && $table->hasField('checked_out_time');

        // Attempt to check-out the new record. If failed, display a notice but allow the user to see the record
        if ($checkin && !$this->attemptCheckout($model, $recordId, $urlVar)) {
            return false;
        }

        // Check-out succeeded, push the new record id into the session.
        $this->holdEditId($context, $recordId);
        $this->app->setUserState($context . '.data', null);

        $this->setRedirect($this->getRedirectUrlToItem($recordId, $urlVar));

        return true;
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  BaseDatabaseModel  The model.
     *
     * @since   1.6
     */
    public function getModel($name = '', $prefix = '', $config = ['ignore_request' => true])
    {
        if (empty($name)) {
            $name = $this->context;
        }

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Gets the redirect URL to an item.
     *
     * @param   integer  $recordId  The primary key id for the item.
     * @param   string   $urlVar    The name of the URL variable for the id.
     *
     * @return  string  The redirect URL to the item.
     *
     * @since   6.1.0
     */
    protected function getRedirectUrlToItem($recordId = null, $urlVar = 'id'): string
    {
        return Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item
            . $this->getRedirectToItemAppend($recordId, $urlVar), false);
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   integer  $recordId  The primary key id for the item.
     * @param   string   $urlVar    The name of the URL variable for the id.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   1.6
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        $append = '';

        // Setup redirect info.
        if ($tmpl = $this->input->get('tmpl', '', 'string')) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout = $this->input->get('layout', 'edit', 'string')) {
            $append .= '&layout=' . $layout;
        }

        if ($forcedLanguage = $this->input->get('forcedLanguage', '', 'cmd')) {
            $append .= '&forcedLanguage=' . $forcedLanguage;
        }

        if ($recordId) {
            $append .= '&' . $urlVar . '=' . $recordId;
        }

        $return = $this->input->get('return', null, 'base64');

        if ($return) {
            $append .= '&return=' . $return;
        }

        return $append;
    }

    /**
     * Gets the redirect URL to a list.
     *
     * @return  string  The redirect URL to the list.
     *
     * @since   6.1.0
     */
    protected function getRedirectUrlToList(): string
    {
        return Route::_(
            'index.php?option=' . $this->option . '&view=' . $this->view_list
            . $this->getRedirectToListAppend(),
            false
        );
    }

    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   1.6
     */
    protected function getRedirectToListAppend()
    {
        $append = '';

        // Setup redirect info.
        if ($tmpl = $this->input->get('tmpl', '', 'string')) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($forcedLanguage = $this->input->get('forcedLanguage', '', 'cmd')) {
            $append .= '&forcedLanguage=' . $forcedLanguage;
        }

        return $append;
    }

    /**
     * Function that allows child controller access and modify to model data
     * before the data is saved. The method must return the modified $validData array
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  array
     *
     * @since   6.1.0
     */
    protected function preSaveHook(BaseDatabaseModel $model, array $validData = []): array
    {
        return $validData;
    }

    /**
     * Function that allows child controller access to model data
     * after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
    }

    /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     *
     * @since   1.6
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        $model   = $this->getModel();
        $table   = $model->getTable();
        $data    = $this->input->post->get('jform', [], 'array');
        $checkin = $table->hasField('checked_out') && $table->hasField('checked_out_time');
        $context = "$this->option.edit.$this->context";
        $task    = $this->getTask();

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = (int) $this->input->getInt($urlVar);

        // Populate the row id from the session.
        $data[$key] = $recordId;

        // Give child classes a chance to preprocess the data.
        $data = $this->preprocessSaveData($data);

        // The save2copy task needs to be handled slightly differently.
        if ($task === 'save2copy') {
            // Check-in the original row. If failed, go back to the record and display a notice
            if ($checkin && !$this->attemptCheckin($model, $recordId, $urlVar)) {
                return false;
            }

            // Reset the ID, the multilingual associations and then treat the request as for Apply.
            $data[$key]           = 0;
            $data['associations'] = [];
            $task                 = 'apply';
        }

        // Access check.
        if (!$this->allowSave($data, $key)) {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

            $this->setRedirect($this->getRedirectUrlToList());

            return false;
        }

        // Validate the posted data.
        // Sometimes the form needs some posted data, such as for plugins and modules.
        $form = $model->getForm($data, false);

        if (!$form) {
            $this->app->enqueueMessage($model->getError(), CMSWebApplicationInterface::MSG_ERROR);

            return false;
        }

        // Allow plugins to normalize/modify the data before validation.
        $data = $this->normalizeRequestData($form, $data);

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            // Push up to validation error messages out to the user.
            $this->handleSaveDataValidationErrorMessages($model->getErrors());

            /**
             * We need the filtered value of calendar fields because the UTC normalisation is
             * done in the filter and on output. This would apply the Timezone offset on
             * reload. We set the calendar values we save to the processed date.
             */
            $data = $this->applyFilterForCalendarFieldsToRequestData($form, $data);

            // Save the data in the session.
            $this->app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect($this->getRedirectUrlToItem($recordId, $urlVar));

            return false;
        }

        if (!isset($validData['tags'])) {
            $validData['tags'] = [];
        }

        // Invoke the preSaveHook method to allow for the child class to modify $validData before save.
        $validData = $this->preSaveHook($model, $validData);

        // Attempt to save the data.
        if (!$model->save($validData)) {
            // Save the data in the session.
            $this->app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
            $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');

            $this->setRedirect($this->getRedirectUrlToItem($recordId, $urlVar));

            return false;
        }

        // Save succeeded, so check-in the record.
        if ($checkin && $validData[$key] && !$this->attemptCheckin($model, $validData[$key], $urlVar)) {
            // Save the data in the session.
            $this->app->setUserState($context . '.data', $validData);

            return false;
        }

        // Set the message after successful save.
        $this->setSaveSuccessMessage($recordId);

        // Redirect the user and adjust session state based on the chosen task.
        switch ($task) {
            case 'apply':
                // Set the record data in the session.
                $recordId = $model->getState($model->getName() . '.id');
                $this->holdEditId($context, $recordId);
                $this->app->setUserState($context . '.data', null);
                $model->checkout($recordId);

                // Redirect back to the edit screen.
                $this->setRedirect($this->getRedirectUrlToItem($recordId, $urlVar));
                break;

            case 'save2new':
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $this->app->setUserState($context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect($this->getRedirectUrlToItem(null, $urlVar));
                break;

            default:
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $this->app->setUserState($context . '.data', null);

                // Check if there is a return value
                $return = $this->input->get('return', null, 'base64');

                if (!\is_null($return) && Uri::isInternal(base64_decode($return))) {
                    // Route the provided return URL if internal
                    $this->setRedirect(Route::_(base64_decode($return), false));
                } else {
                    // Otherwise use the standard list URL helper.
                    $this->setRedirect($this->getRedirectUrlToList());
                }
                break;
        }

        // Invoke the postSave method to allow for the child class to access the model.
        $this->postSaveHook($model, $validData);

        return true;
    }

    /**
     * Method to reload a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  void
     *
     * @since   3.7.4
     */
    public function reload($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        $model   = $this->getModel();
        $data    = $this->input->post->get('jform', [], 'array');

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $model->getTable()->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = $this->input->getInt($urlVar);

        // Populate the row id from the session.
        $data[$key] = $recordId;

        // Check if it is allowed to edit or create the data
        if (($recordId && !$this->allowEdit($data, $key)) || (!$recordId && !$this->allowAdd($data))) {
            $this->setRedirect($this->getRedirectUrlToList());
            $this->redirect();
        }

        // The redirect url
        $redirectUrl = $this->getRedirectUrlToItem($recordId, $urlVar);

        /** @var \Joomla\CMS\Form\Form $form */
        $form = $model->getForm($data, false);

        /**
         * We need the filtered value of calendar fields because the UTC normalisation is
         * done in the filter and on output. This would apply the Timezone offset on
         * reload. We set the calendar values we save to the processed date.
         */
        $data = $this->applyFilterForCalendarFieldsToRequestData($form, $data);

        // Save the data in the session.
        $this->app->setUserState($this->option . '.edit.' . $this->context . '.data', $data);

        $this->setRedirect($redirectUrl);
        $this->redirect();
    }

    /**
     * Method to preprocess data gotten from the request before further processing. Child controllers can use this to
     * manipulate data before checking access, validation, and saving.
     *
     * @param   array  $data  The data array.
     *
     * @return  array  The processed data array.
     *
     * @since   6.1.0
     */
    protected function preprocessSaveData(array $data): array
    {
        return $data;
    }

    /**
     * Method to perform checkin of a record.
     *
     * @param   BaseDatabaseModel  $model     The model object.
     * @param   string|int         $recordId  The primary key id for the item.
     * @param   string             $urlVar    The name of the URL variable for the id.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since   6.1.0
     */
    protected function attemptCheckin(BaseDatabaseModel $model, $recordId, $urlVar): bool
    {
        if ($model->checkin($recordId) === false) {
            // Check-in failed, go back to the record and display a notice.
            $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'error');

            $this->setRedirect($this->getRedirectUrlToItem($recordId, $urlVar));

            return false;
        }

        return true;
    }

    /**
     * Method to perform checkout of a record.
     *
     * @param   BaseDatabaseModel  $model     The model object.
     * @param   string|int         $recordId  The primary key id for the item.
     * @param   string             $urlVar    The name of the URL variable for the id.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since   6.1.0
     */
    protected function attemptCheckout(BaseDatabaseModel $model, $recordId, $urlVar): bool
    {
        if ($model->checkout($recordId) === false) {
            // Check-out failed, display a notice but allow the user to see the record.
            $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError()), 'error');

            $this->setRedirect($this->getRedirectUrlToItem($recordId, $urlVar));

            return false;
        }

        return true;
    }

    /**
     * Method to normalize the request data through the plugin event.
     *
     * @param   Form   $form  The form object.
     * @param   array  $data  The data array.
     *
     * @return  array  The normalized data.
     *
     * @since   6.1.0
     */
    protected function normalizeRequestData(Form $form, array $data): array
    {
        // Send an object which can be modified through the plugin event
        $objData = (object) $data;
        $this->getDispatcher()->dispatch(
            'onContentNormaliseRequestData',
            new Model\NormaliseRequestDataEvent('onContentNormaliseRequestData', [
                'context' => $this->option . '.' . $this->context,
                'data'    => $objData,
                'subject' => $form,
            ])
        );

        return (array) $objData;
    }

    /**
     * Method to handle save data validation errors.
     *
     * @param   array  $errors  The array of validation errors.
     *
     * @return  void
     *
     * @since   6.1.0
     */
    protected function handleSaveDataValidationErrorMessages(array $errors): void
    {
        // Push up to three validation messages out to the user.
        for ($i = 0, $n = \count($errors); $i < $n && $i < 3; $i++) {
            if ($errors[$i] instanceof \Exception) {
                $this->app->enqueueMessage($errors[$i]->getMessage(), CMSApplicationInterface::MSG_ERROR);
            } else {
                $this->app->enqueueMessage($errors[$i], CMSApplicationInterface::MSG_ERROR);
            }
        }
    }

    /**
     * Method to apply filter and merge filtered calendar fields data to request data.
     *
     * @param   Form   $form  The form object.
     * @param   array  $data  The request data array.
     *
     * @return  array  The request data array with filter applied for calendar fields.
     *
     * @since   6.1.0
     */
    protected function applyFilterForCalendarFieldsToRequestData(Form $form, array $data): array
    {
        $filteredData = $form->filter($data);

        foreach ($form->getFieldset() as $field) {
            if ($field->type === 'Calendar') {
                $fieldName = $field->fieldname;

                if ($field->group) {
                    if (isset($filteredData[$field->group][$fieldName])) {
                        $data[$field->group][$fieldName] = $filteredData[$field->group][$fieldName];
                    }
                } else {
                    if (isset($filteredData[$fieldName])) {
                        $data[$fieldName] = $filteredData[$fieldName];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Method to set the save success message.
     *
     * @param   int  $recordId  The record id.
     *
     * @return  void
     *
     * @since   6.1.0
     */
    protected function setSaveSuccessMessage($recordId): void
    {
        $suffix = ($recordId === 0 && $this->app->isClient('site'))
            ? '_SUBMIT_SAVE_SUCCESS'
            : '_SAVE_SUCCESS';

        $langKey = $this->text_prefix . $suffix;

        $prefix = $this->app->getLanguage()->hasKey($langKey)
            ? $this->text_prefix
            : 'JLIB_APPLICATION';

        $this->setMessage(Text::_($prefix . $suffix));
    }

    /**
     * Load item to edit associations in com_associations
     *
     * @return  void
     *
     * @since   3.9.0
     *
     * @deprecated  4.3 will be removed in 7.0
     *              It is handled by regular save method now.
     */
    public function editAssociations()
    {
        // Initialise variables.
        $model = $this->getModel();
        $data  = $this->input->get('jform', [], 'array');

        $model->editAssociations($data);
    }
}
