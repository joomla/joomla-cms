<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_contact_form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ContactForm\Site\Helper;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Event\Contact\SubmitContactEvent;
use Joomla\CMS\Event\Contact\ValidateContactEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_contact_form
 *
 * @since   __DEPLOY_VERSION__
 */
class ContactFormHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    private const MODULE_ELEMENT = 'mod_contact_form';

    /**
     * Get the data required to render the contact form module instance.
     *
     * Returns ['render' => false] when the configured contact is unavailable,
     * unpublished, inaccessible, or has the email form disabled.
     *
     * @param   Registry                 $params  The module parameters.
     * @param   object                   $module  The module object.
     * @param   CMSApplicationInterface  $app     The current application.
     *
     * @return  array  The form rendering data.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getFormData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $contactId = (int) $params->get('contact_id', 0);

        if (!$contactId) {
            return ['render' => false];
        }

        // The form (contact.xml) is owned by com_contact and its field labels resolve
        // against COM_CONTACT_* keys, so its frontend language file must be loaded
        // explicitly when rendered outside its own component context.
        $app->getLanguage()->load('com_contact', JPATH_SITE);

        $contactComponent = $app->bootComponent('com_contact');
        $model            = $contactComponent->getMVCFactory()->createModel('Contact', 'Site', ['ignore_request' => true]);

        // ignore_request => true skips ContactModel::populateState(), which is the only
        // place that sets state.params. getItem()/getForm() then fail on `clone null`.
        $model->setState('params', $app->getParams('com_contact'));
        $model->setState('filter.published', 1);

        $contact = $model->getItem($contactId);

        if (!$contact || (int) $contact->published !== 1) {
            return ['render' => false];
        }

        if ((int) $contact->params->get('show_email_form', 1) === 0) {
            return ['render' => false];
        }

        $model->setState('contact.id', $contact->id);

        // FormBehaviorTrait resolves form paths via JPATH_COMPONENT, which points at
        // the dispatched component (the module, in this context) — not com_contact.
        // Register the contact forms path explicitly so contact.xml can be loaded.
        Form::addFormPath(JPATH_SITE . '/components/com_contact/forms');
        $form = $model->getForm([], false);

        if (!$form) {
            return ['render' => false];
        }

        $captchaEnabled = $this->resolveCaptcha($params, $contact, $app);

        return [
            'render'         => true,
            'contact'        => $contact,
            'form'           => $form,
            'captchaEnabled' => $captchaEnabled,
        ];
    }

    /**
     * Submit the contact form through the AJAX endpoint.
     *
     * The application argument defaults to null so com_ajax can invoke the
     * method without passing arguments.
     *
     * @param   CMSApplicationInterface|null  $app  The current application.
     *
     * @return  array  The AJAX response payload.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function submitAjax(?CMSApplicationInterface $app = null): array
    {
        $app ??= Factory::getApplication();

        if (!Session::checkToken('post')) {
            return [
                'ok'     => false,
                'errors' => ['general' => Text::_('JINVALID_TOKEN')],
                'token'  => Session::getFormToken(),
            ];
        }

        if (!$app->isClient('site')) {
            return [
                'ok'     => false,
                'errors' => ['general' => Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN')],
                'token'  => Session::getFormToken(),
            ];
        }

        $input         = $app->getInput();
        $moduleId      = $input->post->getInt('module_id', 0);
        $postContactId = $input->post->getInt('contact_id', 0);

        $user       = $app->getIdentity();
        $userLevels = $user ? $user->getAuthorisedViewLevels() : [1];

        $db     = $this->getDatabase();
        $module = self::MODULE_ELEMENT;

        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'params', 'published', 'access']))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('id') . ' = :id')
            ->where($db->quoteName('module') . ' = :module')
            ->where($db->quoteName('client_id') . ' = 0')
            ->whereIn($db->quoteName('access'), $userLevels)
            ->bind(':id', $moduleId, ParameterType::INTEGER)
            ->bind(':module', $module);

        $db->setQuery($query);
        $moduleRow = $db->loadObject();

        if (!$moduleRow || (int) $moduleRow->published !== 1) {
            return [
                'ok'     => false,
                'errors' => ['general' => Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN')],
                'token'  => Session::getFormToken(),
            ];
        }

        $moduleParams     = new Registry($moduleRow->params);
        $trustedContactId = (int) $moduleParams->get('contact_id', 0);

        if ($postContactId !== $trustedContactId) {
            return [
                'ok'     => false,
                'errors' => ['general' => Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN')],
                'token'  => Session::getFormToken(),
            ];
        }

        // Load com_contact frontend language file — needed for field validation
        // messages, COM_CONTACT_EMAIL_THANKS success string, and any plugin output
        // that uses COM_CONTACT_* keys.
        $app->getLanguage()->load('com_contact', JPATH_SITE);

        $contactComponent = $app->bootComponent('com_contact');
        $model            = $contactComponent->getMVCFactory()->createModel('Contact', 'Site', ['ignore_request' => true]);

        $model->setState('params', $app->getParams('com_contact'));
        $model->setState('filter.published', 1);
        $contact = $model->getItem($trustedContactId);

        if (
            !$contact
            || (int) $contact->published !== 1
            || (int) $contact->params->get('show_email_form', 1) === 0
            || !\in_array((int) $contact->access, $userLevels, true)
        ) {
            return [
                'ok'     => false,
                'errors' => ['general' => Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN')],
                'token'  => Session::getFormToken(),
            ];
        }

        PluginHelper::importPlugin('contact');

        $model->setState('contact.id', $contact->id);

        Form::addFormPath(JPATH_SITE . '/components/com_contact/forms');
        $form = $model->getForm([], false);

        if (!$form) {
            return [
                'ok'     => false,
                'errors' => ['general' => Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN')],
                'token'  => Session::getFormToken(),
            ];
        }

        $data = $input->post->get('jform', [], 'array');

        if (!$model->validate($form, $data)) {
            $errorFields = [];

            foreach ($model->getErrors() as $error) {
                if ($error instanceof \RuntimeException) {
                    $errorFields['general'] = $error->getMessage();
                } elseif ($error instanceof \Exception) {
                    Log::add($error->getMessage(), Log::WARNING, self::MODULE_ELEMENT);
                    $errorFields['general'] = Text::_('MOD_CONTACT_FORM_MESSAGE_VALIDATION_FAILED');
                } else {
                    $errorFields['general'] = (string) $error;
                }
            }

            return [
                'ok'     => false,
                'errors' => $errorFields ?: ['general' => Text::_('MOD_CONTACT_FORM_MESSAGE_VALIDATION_FAILED')],
                'token'  => Session::getFormToken(),
            ];
        }

        $dispatcher = $app->getDispatcher();

        $results = $dispatcher->dispatch('onValidateContact', new ValidateContactEvent('onValidateContact', [
            'subject' => $contact,
            'data'    => &$data,
        ]))->getArgument('result', []);

        foreach ($results as $result) {
            if ($result instanceof \Exception) {
                Log::add($result->getMessage(), Log::WARNING, self::MODULE_ELEMENT);

                return [
                    'ok'     => false,
                    'errors' => ['general' => Text::_('MOD_CONTACT_FORM_MESSAGE_VALIDATION_FAILED')],
                    'token'  => Session::getFormToken(),
                ];
            }
        }

        $event = $dispatcher->dispatch('onSubmitContact', new SubmitContactEvent('onSubmitContact', [
            'subject' => $contact,
            'data'    => &$data,
        ]));
        $data = $event->getArgument('data', $data);

        if ($contact->email_to === '' && (int) $contact->user_id !== 0) {
            $userFactory       = Factory::getContainer()->get(UserFactoryInterface::class);
            $contactUser       = $userFactory->loadUserById((int) $contact->user_id);
            $contact->email_to = $contactUser->email;
        }

        $templateData = [
            'sitename'     => $app->get('sitename'),
            'name'         => $data['contact_name'] ?? '',
            'contactname'  => $contact->name,
            'email'        => PunycodeHelper::emailToPunycode($data['contact_email'] ?? ''),
            'subject'      => $data['contact_subject'] ?? '',
            'body'         => stripslashes($data['contact_message'] ?? ''),
            'url'          => Uri::base(),
            'customfields' => '',
        ];

        if (!empty($data['com_fields']) && $fields = FieldsHelper::getFields('com_contact.mail', $contact, true, $data['com_fields'])) {
            $output = FieldsHelper::render('com_contact.mail', 'fields.render', [
                'context' => 'com_contact.mail',
                'item'    => $contact,
                'fields'  => $fields,
            ]);

            if ($output) {
                $templateData['customfields'] = $output;
            }
        }

        try {
            $lang   = $app->getLanguage();
            $mailer = new MailTemplate('com_contact.mail', $lang->getTag());
            $mailer->addRecipient($contact->email_to);
            $mailer->setReplyTo($templateData['email'], $templateData['name']);
            $mailer->addTemplateData($templateData);
            $mailer->addUnsafeTags(['name', 'email', 'body']);
            $mailer->send();

            if ($contact->params->get('show_email_copy', 0) && !empty($data['contact_email_copy'])) {
                $copyMailer = new MailTemplate('com_contact.mail.copy', $lang->getTag());
                $copyMailer->addRecipient($templateData['email']);
                $copyMailer->setReplyTo($templateData['email'], $templateData['name']);
                $copyMailer->addTemplateData($templateData);
                $copyMailer->addUnsafeTags(['name', 'email', 'body']);
                $copyMailer->send();
            }
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, self::MODULE_ELEMENT);

            return [
                'ok'     => false,
                'errors' => ['general' => Text::_('MOD_CONTACT_FORM_MESSAGE_NETWORK_ERROR')],
                'token'  => Session::getFormToken(),
            ];
        }

        $successMessage = trim((string) $moduleParams->get('success_message', ''));

        if ($successMessage === '') {
            $successMessage = Text::_('COM_CONTACT_EMAIL_THANKS');
        }

        $redirectUrl = trim((string) $moduleParams->get('redirect_on_success', ''));

        return [
            'ok'       => true,
            'message'  => $successMessage,
            'redirect' => $redirectUrl !== '' ? $redirectUrl : null,
            'token'    => Session::getFormToken(),
        ];
    }

    /**
     * Determine whether a configured captcha plugin is available.
     *
     * @param   Registry                 $params  The module parameters.
     * @param   CMSApplicationInterface  $app     The current application.
     *
     * @return  bool  True when the configured captcha plugin exists.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function resolveCaptcha(Registry $params, object $contact, CMSApplicationInterface $app): bool
    {
        $captchaSet = $params->get('captcha', $app->get('captcha', '0'));

        if ($captchaSet === 'default') {
            $captchaSet = $contact->params->get('captcha', $app->get('captcha', '0'));
        }

        foreach (PluginHelper::getPlugin('captcha') as $plugin) {
            if ($captchaSet === $plugin->name) {
                return true;
            }
        }

        return false;
    }
}
