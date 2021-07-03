<?php
/**
 * @package     Joomla.API
 * @subpackage  com_contact
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Controller\Exception\SendEmail;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Registry\Registry;
use Joomla\String\Inflector;
use PHPMailer\PHPMailer\Exception as phpMailerException;
use Tobscure\JsonApi\Exception\InvalidParameterException;

/**
 * The contact controller
 *
 * @since  4.0.0
 */
class ContactController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'contacts';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'contacts';

	/**
	 * Method to allow extended classes to manipulate the data to be saved for an extension.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected function preprocessSaveData(array $data): array
	{
		foreach (FieldsHelper::getFields('com_contact.contact') as $field)
		{
			if (isset($data[$field->name]))
			{
				!isset($data['com_fields']) && $data['com_fields'] = [];

				$data['com_fields'][$field->name] = $data[$field->name];
				unset($data[$field->name]);
			}
		}

		return $data;
	}

	/**
	 * Submit contact form
	 *
	 * @param   integer  $id Leave empty if you want to retrieve data from the request
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function submitForm($id = null)
	{
		if ($id === null)
		{
			$id = $this->input->post->get('id', 0, 'int');
		}

		$modelName = Inflector::singularize($this->contentType);

		/** @var  \Joomla\Component\Contact\Site\Model\ContactModel $model */
		$model = $this->getModel($modelName, 'Site');

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		$model->setState('filter.published', 1);

		$data    = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');
		$contact = $model->getItem($id);

		if ($contact->id === null)
		{
			throw new RouteNotFoundException('Item does not exist');
		}

		$contactParams = new Registry($contact->params);

		if (!$contactParams->get('show_email_form'))
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_DISPLAY_EMAIL_FORM'));
		}

		// Contact plugins
		PluginHelper::importPlugin('contact');

		Form::addFormPath(JPATH_COMPONENT_SITE . '/forms');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new \RuntimeException($model->getError(), 500);
		}

		if (!$model->validate($form, $data))
		{
			$errors   = $model->getErrors();
			$messages = [];

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$messages[] = "{$errors[$i]->getMessage()}";
				}
				else
				{
					$messages[] = "{$errors[$i]}";
				}
			}

			throw new InvalidParameterException(implode("\n", $messages));
		}

		// Validation succeeded, continue with custom handlers
		$results = $this->app->triggerEvent('onValidateContact', [&$contact, &$data]);

		foreach ($results as $result)
		{
			if ($result instanceof \Exception)
			{
				throw new InvalidParameterException($result->getMessage());
			}
		}

		// Passed Validation: Process the contact plugins to integrate with other applications
		$this->app->triggerEvent('onSubmitContact', [&$contact, &$data]);

		// Send the email
		$sent = false;

		$params = ComponentHelper::getParams('com_contact');

		if (!$params->get('custom_reply'))
		{
			$sent = $this->_sendEmail($data, $contact, $params->get('show_email_copy', 0));
		}

		if (!$sent)
		{
			throw new SendEmail('Error sending message');
		}

		return $this;
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

		Factory::getLanguage()->load('com_contact', JPATH_SITE, $app->getLanguage()->getTag(), true);

		if ($contact->email_to == '' && $contact->user_id != 0)
		{
			$contact_user      = User::getInstance($contact->user_id);
			$contact->email_to = $contact_user->get('email');
		}

		$templateData = [
			'sitename' => $app->get('sitename'),
			'name'     => $data['contact_name'],
			'contactname' => $contact->name,
			'email'    => PunycodeHelper::emailToPunycode($data['contact_email']),
			'subject'  => $data['contact_subject'],
			'body'     => stripslashes($data['contact_message']),
			'url'      => Uri::base(),
			'customfields' => ''
		];

		// Load the custom fields
		if (!empty($data['com_fields']) && $fields = FieldsHelper::getFields('com_contact.mail', $contact, true, $data['com_fields']))
		{
			$output = FieldsHelper::render(
				'com_contact.mail',
				'fields.render',
				array(
					'context' => 'com_contact.mail',
					'item'    => $contact,
					'fields'  => $fields,
				)
			);

			if ($output)
			{
				$templateData['customfields'] = $output;
			}
		}

		try
		{
			$mailer = new MailTemplate('com_contact.mail', $app->getLanguage()->getTag());
			$mailer->addRecipient($contact->email_to);
			$mailer->setReplyTo($templateData['email'], $templateData['name']);
			$mailer->addTemplateData($templateData);
			$sent = $mailer->send();

			// If we are supposed to copy the sender, do so.
			if ($emailCopyToSender == true && !empty($data['contact_email_copy']))
			{
				$mailer = new MailTemplate('com_contact.mail.copy', $app->getLanguage()->getTag());
				$mailer->addRecipient($templateData['email']);
				$mailer->setReplyTo($templateData['email'], $templateData['name']);
				$mailer->addTemplateData($templateData);
				$sent = $mailer->send();
			}
		}
		catch (MailDisabledException | phpMailerException $exception)
		{
			try
			{
				Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

				$sent = false;
			}
			catch (\RuntimeException $exception)
			{
				Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

				$sent = false;
			}
		}

		return $sent;
	}
}
