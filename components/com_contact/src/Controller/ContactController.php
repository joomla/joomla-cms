<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Utilities\ArrayHelper;
use PHPMailer\PHPMailer\Exception as phpMailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for single contact view
 *
 * @since  1.5.19
 */
class ContactController extends FormController
{
    use VersionableControllerTrait;

    /**
     * The URL view item variable.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $view_item = 'form';

    /**
     * The URL view list variable.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $view_list = 'categories';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     *
     * @since   1.6.4
     */
    public function getModel($name = 'form', $prefix = '', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => false]);
    }

    /**
     * Method to submit the contact form and send an email.
     *
     * @return  boolean  True on success sending the email. False on failure.
     *
     * @since   1.5.19
     */
    public function submit()
    {
        // Check for request forgeries.
        $this->checkToken();

        $app    = $this->app;
        $model  = $this->getModel('contact');
        $stub   = $this->input->getString('id');
        $id     = (int) $stub;

        // Get the data from POST
        $data = $this->input->post->get('jform', [], 'array');

        // Get item
        $model->setState('filter.published', 1);
        $contact = $model->getItem($id);

        if ($contact === false) {
            $this->setMessage($model->getError(), 'error');

            return false;
        }

        // Get item params, take menu parameters into account if necessary
        $active      = $app->getMenu()->getActive();
        $stateParams = clone $model->getState()->get('params');

        // If the current view is the active item and a contact view for this contact, then the menu item params take priority
        if ($active && strpos($active->link, 'view=contact') && strpos($active->link, '&id=' . (int) $contact->id)) {
            // $item->params are the contact params, $temp are the menu item params
            // Merge so that the menu item params take priority
            $contact->params->merge($stateParams);
        } else {
            // Current view is not a single contact, so the contact params take priority here
            $stateParams->merge($contact->params);
            $contact->params = $stateParams;
        }

        // Check if the contact form is enabled
        if (!$contact->params->get('show_email_form')) {
            $this->setRedirect(Route::_('index.php?option=com_contact&view=contact&id=' . $stub . '&catid=' . $contact->catid, false));

            return false;
        }

        // Check for a valid session cookie
        if ($contact->params->get('validate_session', 0)) {
            if (Factory::getSession()->getState() !== 'active') {
                $this->app->enqueueMessage(Text::_('JLIB_ENVIRONMENT_SESSION_INVALID'), 'warning');

                // Save the data in the session.
                $this->app->setUserState('com_contact.contact.data', $data);

                // Redirect back to the contact form.
                $this->setRedirect(Route::_('index.php?option=com_contact&view=contact&id=' . $stub . '&catid=' . $contact->catid, false));

                return false;
            }
        }

        // Contact plugins
        PluginHelper::importPlugin('contact');

        // Validate the posted data.
        $form = $model->getForm();

        if (!$form) {
            throw new \Exception($model->getError(), 500);
        }

        if (!$model->validate($form, $data)) {
            $errors = $model->getErrors();

            foreach ($errors as $error) {
                $errorMessage = $error;

                if ($error instanceof \Exception) {
                    $errorMessage = $error->getMessage();
                }

                $app->enqueueMessage($errorMessage, 'error');
            }

            $app->setUserState('com_contact.contact.data', $data);

            $this->setRedirect(Route::_('index.php?option=com_contact&view=contact&id=' . $stub . '&catid=' . $contact->catid, false));

            return false;
        }

        // Validation succeeded, continue with custom handlers
        $results = $this->app->triggerEvent('onValidateContact', [&$contact, &$data]);

        $passValidation = true;

        foreach ($results as $result) {
            if ($result instanceof \Exception) {
                $passValidation = false;
                $app->enqueueMessage($result->getMessage(), 'error');
            }
        }

        if (!$passValidation) {
            $app->setUserState('com_contact.contact.data', $data);

            $this->setRedirect(Route::_('index.php?option=com_contact&view=contact&id=' . $id . '&catid=' . $contact->catid, false));

            return false;
        }

        // Passed Validation: Process the contact plugins to integrate with other applications
        $this->app->triggerEvent('onSubmitContact', [&$contact, &$data]);

        // Send the email
        $sent = false;

        if (!$contact->params->get('custom_reply')) {
            $sent = $this->_sendEmail($data, $contact, $contact->params->get('show_email_copy', 0));
        }

        $msg = '';

        // Set the success message if it was a success
        if ($sent) {
            $msg = Text::_('COM_CONTACT_EMAIL_THANKS');
        }

        // Flush the data from the session
        $this->app->setUserState('com_contact.contact.data', null);

        // Redirect if it is set in the parameters, otherwise redirect back to where we came from
        if ($contact->params->get('redirect')) {
            $this->setRedirect($contact->params->get('redirect'), $msg);
        } else {
            $this->setRedirect(Route::_('index.php?option=com_contact&view=contact&id=' . $stub . '&catid=' . $contact->catid, false), $msg);
        }

        return true;
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   array      $data               The data to send in the email.
     * @param   \stdClass  $contact            The user information to send the email to
     * @param   boolean    $emailCopyToSender  True to send a copy of the email to the user.
     *
     * @return  boolean  True on success sending the email, false on failure.
     *
     * @since   1.6.4
     */
    private function _sendEmail($data, $contact, $emailCopyToSender)
    {
        $app = $this->app;

        if ($contact->email_to == '' && $contact->user_id != 0) {
            $contact_user      = User::getInstance($contact->user_id);
            $contact->email_to = $contact_user->get('email');
        }

        $templateData = [
            'sitename'     => $app->get('sitename'),
            'name'         => $data['contact_name'],
            'contactname'  => $contact->name,
            'email'        => PunycodeHelper::emailToPunycode($data['contact_email']),
            'subject'      => $data['contact_subject'],
            'body'         => stripslashes($data['contact_message']),
            'url'          => Uri::base(),
            'customfields' => '',
        ];

        // Load the custom fields
        if (!empty($data['com_fields']) && $fields = FieldsHelper::getFields('com_contact.mail', $contact, true, $data['com_fields'])) {
            $output = FieldsHelper::render(
                'com_contact.mail',
                'fields.render',
                [
                    'context' => 'com_contact.mail',
                    'item'    => $contact,
                    'fields'  => $fields,
                ]
            );

            if ($output) {
                $templateData['customfields'] = $output;
            }
        }

        try {
            $mailer = new MailTemplate('com_contact.mail', $app->getLanguage()->getTag());
            $mailer->addRecipient($contact->email_to);
            $mailer->setReplyTo($templateData['email'], $templateData['name']);
            $mailer->addTemplateData($templateData);
            $sent = $mailer->send();

            // If we are supposed to copy the sender, do so.
            if ($emailCopyToSender == true && !empty($data['contact_email_copy'])) {
                $mailer = new MailTemplate('com_contact.mail.copy', $app->getLanguage()->getTag());
                $mailer->addRecipient($templateData['email']);
                $mailer->setReplyTo($templateData['email'], $templateData['name']);
                $mailer->addTemplateData($templateData);
                $sent = $mailer->send();
            }
        } catch (MailDisabledException | phpMailerException $exception) {
            try {
                Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

                $sent = false;
            } catch (\RuntimeException $exception) {
                $this->app->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

                $sent = false;
            }
        }

        return $sent;
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function allowAdd($data = [])
    {
        if ($categoryId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('catid'), 'int')) {
            $user = $this->app->getIdentity();

            // If the category has been passed in the data or URL check it.
            return $user->authorise('core.create', 'com_contact.category.' . $categoryId);
        }

        // In the absence of better information, revert to the component permissions.
        return parent::allowAdd();
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;

        if (!$recordId) {
            return false;
        }

        // Need to do a lookup from the model.
        $record     = $this->getModel()->getItem($recordId);
        $categoryId = (int) $record->catid;

        if ($categoryId) {
            $user = $this->app->getIdentity();

            // The category has been set. Check the category permissions.
            if ($user->authorise('core.edit', $this->option . '.category.' . $categoryId)) {
                return true;
            }

            // Fallback on edit.own.
            if ($user->authorise('core.edit.own', $this->option . '.category.' . $categoryId)) {
                return ($record->created_by === $user->id);
            }

            return false;
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
    }

    /**
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     *
     * @since   4.0.0
     */
    public function cancel($key = null)
    {
        $result = parent::cancel($key);

        $this->setRedirect(Route::_($this->getReturnPage(), false));

        return $result;
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   integer  $recordId  The primary key id for the item.
     * @param   string   $urlVar    The name of the URL variable for the id.
     *
     * @return  string    The arguments to append to the redirect URL.
     *
     * @since   4.0.0
     */
    protected function getRedirectToItemAppend($recordId = 0, $urlVar = 'id')
    {
        // Need to override the parent method completely.
        $tmpl = $this->input->get('tmpl');

        $append = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        $append .= '&layout=edit';

        $append .= '&' . $urlVar . '=' . (int) $recordId;

        $itemId = $this->input->getInt('Itemid');
        $return = $this->getReturnPage();
        $catId  = $this->input->getInt('catid');

        if ($itemId) {
            $append .= '&Itemid=' . $itemId;
        }

        if ($catId) {
            $append .= '&catid=' . $catId;
        }

        if ($return) {
            $append .= '&return=' . base64_encode($return);
        }

        return $append;
    }

    /**
     * Get the return URL.
     *
     * If a "return" variable has been passed in the request
     *
     * @return  string    The return URL.
     *
     * @since   4.0.0
     */
    protected function getReturnPage()
    {
        $return = $this->input->get('return', null, 'base64');

        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            return Uri::base();
        }

        return base64_decode($return);
    }
}
