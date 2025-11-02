<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel as BaseForm;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Prototype form model.
 *
 * @see    JForm
 * @see    \Joomla\CMS\Form\FormField
 * @see    \Joomla\CMS\Form\FormRule
 * @since  3.2
 */
abstract class FormModel extends BaseForm
{
    /**
     * Array of form objects.
     *
     * @var    array
     * @since  3.2
     */
    protected $forms = [];

    /**
     * Method to checkin a row.
     *
     * @param   integer  $pk  The numeric id of the primary key.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   3.2
     * @throws  \RuntimeException
     */
    public function checkin($pk = null)
    {
        // Only attempt to check the row in if it exists.
        if ($pk) {
            $user = $this->getCurrentUser();

            // Get an instance of the row to checkin.
            $table = $this->getTable();

            if (!$table->load($pk)) {
                throw new \RuntimeException($table->getError());
            }

            // Check if this is the user has previously checked out the row.
            if (!\is_null($table->checked_out) && $table->checked_out != $user->id && !$user->authorise('core.admin', 'com_checkin')) {
                throw new \RuntimeException($table->getError());
            }

            // Attempt to check the row in.
            if (!$table->checkIn($pk)) {
                throw new \RuntimeException($table->getError());
            }
        }

        return true;
    }

    /**
     * Method to check-out a row for editing.
     *
     * @param   integer  $pk  The numeric id of the primary key.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   3.2
     */
    public function checkout($pk = null)
    {
        // Only attempt to check the row in if it exists.
        if ($pk) {
            $user = $this->getCurrentUser();

            // Get an instance of the row to checkout.
            $table = $this->getTable();

            if (!$table->load($pk)) {
                throw new \RuntimeException($table->getError());
            }

            // Check if this is the user having previously checked out the row.
            if (!\is_null($table->checked_out) && $table->checked_out != $user->id) {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));
            }

            // Attempt to check the row out.
            if (!$table->checkOut($user->id, $pk)) {
                throw new \RuntimeException($table->getError());
            }
        }

        return true;
    }

    /**
     * Method to get a form object.
     *
     * @param   string   $name     The name of the form.
     * @param   string   $source   The form source. Can be XML string if file flag is set to false.
     * @param   array    $options  Optional array of options for the form creation.
     * @param   boolean  $clear    Optional argument to force load a new form.
     * @param   string   $xpath    An optional xpath to search for the fields.
     *
     * @return  mixed  JForm object on success, False on error.
     *
     * @see     JForm
     * @since   3.2
     */
    protected function loadForm($name, $source = null, $options = [], $clear = false, $xpath = false)
    {
        // Handle the optional arguments.
        $options['control'] = ArrayHelper::getValue($options, 'control', false);

        // Create a signature hash.
        $hash = sha1($source . serialize($options));

        // Check if we can use a previously loaded form.
        if (isset($this->_forms[$hash]) && !$clear) {
            return $this->_forms[$hash];
        }

        //  Register the paths for the form.
        Form::addFormPath(JPATH_SITE . '/components/com_config/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_config/forms');

        try {
            // Get the form.
            $form = Form::getInstance($name, $source, $options, false, $xpath);

            if (isset($options['load_data']) && $options['load_data']) {
                // Get the data for the form.
                $data = $this->loadFormData();
            } else {
                $data = [];
            }

            // Allow for additional modification of the form, and events to be triggered.
            // We pass the data because plugins may require it.
            $this->preprocessForm($form, $data);

            // Load the data into the form after the plugins have operated.
            $form->bind($data);
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage());

            return false;
        }

        // Store the form for later.
        $this->_forms[$hash] = $form;

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array    The default data is an empty array.
     *
     * @since   3.2
     */
    protected function loadFormData()
    {
        return [];
    }

    /**
     * Method to allow derived classes to preprocess the data.
     *
     * @param   string  $context  The context identifier.
     * @param   mixed   &$data    The data to be processed. It gets altered directly.
     * @param   string  $group    The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function preprocessData($context, &$data, $group = 'content')
    {
        // Get the dispatcher and load the users plugins.
        PluginHelper::importPlugin('content');

        // Trigger the data preparation event.
        Factory::getApplication()->triggerEvent('onContentPrepareData', [$context, $data]);
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param   Form    $form   A Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @see     \Joomla\CMS\Form\FormField
     * @since   3.2
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        // Import the appropriate plugin group.
        PluginHelper::importPlugin($group);

        // Trigger the form preparation event.
        Factory::getApplication()->triggerEvent('onContentPrepareForm', [$form, $data]);
    }

    /**
     * Method to validate the form data.
     *
     * @param   Form    $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  mixed  Array of filtered data if valid, false otherwise.
     *
     * @see     \Joomla\CMS\Form\FormRule
     * @see     \Joomla\CMS\Filter\InputFilter
     * @since   3.2
     */
    public function validate($form, $data, $group = null)
    {
        // Filter and validate the form data.
        $data   = $form->filter($data);
        $return = $form->validate($data, $group);

        // Check for an error.
        if ($return instanceof \Exception) {
            Factory::getApplication()->enqueueMessage($return->getMessage(), 'error');

            return false;
        }

        // Check the validation results.
        if ($return === false) {
            // Get the validation messages from the form.
            foreach ($form->getErrors() as $message) {
                if ($message instanceof \Exception) {
                    $message = $message->getMessage();
                }

                Factory::getApplication()->enqueueMessage($message, 'error');
            }

            return false;
        }

        return $data;
    }
}
