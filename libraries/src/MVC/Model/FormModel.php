<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\CMS\Event\Model;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryAwareInterface;
use Joomla\CMS\Form\FormFactoryAwareTrait;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Prototype form model.
 *
 * @see    Form
 * @see    FormField
 * @see    FormRule
 * @since  1.6
 */
abstract class FormModel extends BaseDatabaseModel implements FormFactoryAwareInterface, FormModelInterface
{
    use FormBehaviorTrait;
    use FormFactoryAwareTrait;

    /**
     * Maps events to plugin groups.
     *
     * @var    array
     * @since  3.6
     */
    protected $events_map = null;

    /**
     * Constructor
     *
     * @param   array                  $config       An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   ?MVCFactoryInterface   $factory      The factory.
     * @param   ?FormFactoryInterface  $formFactory  The form factory.
     *
     * @since   3.6
     * @throws  \Exception
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?FormFactoryInterface $formFactory = null)
    {
        $config['events_map'] = $config['events_map'] ?? [];

        $this->events_map = array_merge(
            ['validate' => 'content'],
            $config['events_map']
        );

        parent::__construct($config, $factory);

        $this->setFormFactory($formFactory);
    }

    /**
     * Method to checkin a row.
     *
     * @param   integer  $pk  The numeric id of the primary key.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   1.6
     */
    public function checkin($pk = null)
    {
        // Only attempt to check the row in if it exists.
        if ($pk) {
            $user = $this->getCurrentUser();

            // Get an instance of the row to checkin.
            $table = $this->getTable();

            if (!$table->load($pk)) {
                $this->setError($table->getError());

                return false;
            }

            // If there is no checked_out or checked_out_time field, just return true.
            if (!$table->hasField('checked_out') || !$table->hasField('checked_out_time')) {
                return true;
            }

            $checkedOutField = $table->getColumnAlias('checked_out');

            // Check if this is the user having previously checked out the row.
            if ($table->$checkedOutField > 0 && $table->$checkedOutField != $user->id && !$user->authorise('core.manage', 'com_checkin')) {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));

                return false;
            }

            // Attempt to check the row in.
            if (!$table->checkIn($pk)) {
                $this->setError($table->getError());

                return false;
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
     * @since   1.6
     */
    public function checkout($pk = null)
    {
        // Only attempt to check the row in if it exists.
        if ($pk) {
            // Get an instance of the row to checkout.
            $table = $this->getTable();

            if (!$table->load($pk)) {
                if ($table->getError() === false) {
                    // There was no error returned, but false indicates that the row did not exist in the db, so probably previously deleted.
                    $this->setError(Text::_('JLIB_APPLICATION_ERROR_NOT_EXIST'));
                } else {
                    $this->setError($table->getError());
                }

                return false;
            }

            // If there is no checked_out or checked_out_time field, just return true.
            if (!$table->hasField('checked_out') || !$table->hasField('checked_out_time')) {
                return true;
            }

            $user = $this->getCurrentUser();

            // When the user is a guest, don't do a checkout
            if (!$user->id) {
                return false;
            }

            $checkedOutField = $table->getColumnAlias('checked_out');

            // Check if this is the user having previously checked out the row.
            if ($table->$checkedOutField > 0 && $table->$checkedOutField != $user->id) {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));

                return false;
            }

            // Attempt to check the row out.
            if (!$table->checkOut($user->id, $pk)) {
                $this->setError($table->getError());

                return false;
            }
        }

        return true;
    }

    /**
     * Method to validate the form data.
     *
     * @param   Form    $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @see     FormRule
     * @see     InputFilter
     * @since   1.6
     */
    public function validate($form, $data, $group = null)
    {
        $dispatcher = $this->getDispatcher();

        // Include the plugins for the delete events.
        PluginHelper::importPlugin($this->events_map['validate'], null, true, $dispatcher);

        if (!empty($dispatcher->getListeners('onUserBeforeDataValidation'))) {
            @trigger_error(
                'The `onUserBeforeDataValidation` event is deprecated and will be removed in 6.0.'
                . 'Use the `onContentValidateData` event instead.',
                E_USER_DEPRECATED
            );

            $data = $dispatcher->dispatch('onUserBeforeDataValidation', new Model\BeforeValidateDataEvent('onUserBeforeDataValidation', [
                'subject' => $form,
                'data'    => &$data, // @todo: Remove reference in Joomla 6, see BeforeValidateDataEvent::__constructor()
            ]))->getArgument('data', $data);
        }

        $data = $dispatcher->dispatch('onContentBeforeValidateData', new Model\BeforeValidateDataEvent('onContentBeforeValidateData', [
            'subject' => $form,
            'data'    => &$data, // @todo: Remove reference in Joomla 6, see AfterRenderModulesEvent::__constructor()
        ]))->getArgument('data', $data);

        // Filter and validate the form data.
        $return = $form->process($data, $group);

        // Check for an error.
        if ($return instanceof \Exception) {
            $this->setError($return->getMessage());

            return false;
        }

        // Check the validation results.
        if ($return === false) {
            // Get the validation messages from the form.
            foreach ($form->getErrors() as $message) {
                $this->setError($message);
            }

            return false;
        }

        $data = $return;

        // Tags B/C break at 3.1.2
        if (!isset($data['tags']) && isset($data['metadata']['tags'])) {
            $data['tags'] = $data['metadata']['tags'];
        }

        return $data;
    }
}
