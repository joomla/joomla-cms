<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.Notification
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Workflow\WorkflowTransitionEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\Workflow\WorkflowPluginTrait;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Workflow Notification Plugin
 *
 * @since  4.0.0
 */
class PlgWorkflowNotification extends CMSPlugin implements SubscriberInterface
{
	use WorkflowPluginTrait;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Loads the CMS Application for direct access
	 *
	 * @var   CMSApplicationInterface
	 * @since 4.0.0
	 */
	protected $app;

	/**
	 * @var    DatabaseDriver
	 *
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return   array
	 *
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepareForm'      => 'onContentPrepareForm',
			'onWorkflowAfterTransition' => 'onWorkflowAfterTransition',
			'onContentBeforeSave'       => 'onContentBeforeSave',
		];
	}

	/**
	 * Check if a transition is being saved and all fields are set correctly.
	 *
	 * @param   Event  $data  The event data ($context, $table, $isNew, $data)
	 *
	 * @return  boolean  True if all necessary fields are filled.
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 */
	public function onContentBeforeSave(Event $data): bool
	{
		$context = $data->getArgument(0);

		if ($context !== 'com_workflow.transition')
		{
			return true;
		}

		$values = $data->getArgument(3);

		if ($values['options']['notification_send_mail'] === false)
		{
			return true;
		}

		if (empty($values['options']['notification_type']))
		{
			throw new InvalidArgumentException(Text::_('PLG_WORKFLOW_NOTIFICATION_NO_NOTIFICATION_TYPE_SELECTED'));
		}

		if (!isset($values['options']['notification_receivers']) && !isset($values['options']['notification_groups'])
			|| (empty($values['options']['notification_receivers']) && empty($values['options']['notification_groups']))
		)
		{
			throw new InvalidArgumentException(Text::_('PLG_WORKFLOW_NOTIFICATION_NO_RECIPIENTS_SELECTED'));
		}

		return true;
	}

	/**
	 * The event contains two arguments:
	 *
	 * Form      $form  The form
	 * stdClass  $data  The data
	 *
	 * @param   EventInterface  $event  The event data
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function onContentPrepareForm(EventInterface $event): bool
	{
		$form = $event->getArgument('0');
		$data = $event->getArgument('1');

		$context = $form->getName();

		// Extend the transition form
		if ($context === 'com_workflow.transition')
		{
			$this->enhanceWorkflowTransitionForm($form, $data);
		}

		return true;
	}

	/**
	 * Send a Notification to defined users a transition is performed
	 *
	 * The event contains several arguments:
	 *
	 * string  $context        The context for the content passed to the plugin.
	 * string  $extensionName  The extension name throwing the trigger event
	 * array   $pks            A list of primary key ids of the content that has changed stage.
	 * object  $data           Object containing data about the transition
	 *
	 * @param   WorkflowTransitionEvent  $event  The event data
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  Exception
	 * @see     sendMessages()
	 * @see     sendEmail()
	 */
	public function onWorkflowAfterTransition(WorkflowTransitionEvent $event): void
	{
		$context       = $event->getArgument('extension');
		$extensionName = $event->getArgument('extensionName');
		$transition    = $event->getArgument('transition');
		$pks           = $event->getArgument('pks');

		if (!$this->isSupported($context))
		{
			return;
		}

		$component = $this->app->bootComponent($extensionName);

		// Check if send-mail is active
		if (empty($transition->options['notification_send_mail']))
		{
			return;
		}

		// ID of the items whose state has changed.
		$pks = ArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			return;
		}

		// Get UserIds of Receivers
		$userIds = $this->getUsersFromGroup($transition);

		// The active user
		$user = $this->app->getIdentity();

		// Prepare Language for messages
		$defaultLanguage = ComponentHelper::getParams('com_languages')->get('administrator');
		$debug           = $this->app->get('debug_lang');

		$modelName = $component->getModelName($context);
		$model     = $component->getMVCFactory()
			->createModel($modelName, $this->app->getName(), ['ignore_request' => true]);

		// Don't send the notification to the active user
		$key = array_search($user->id, $userIds);

		if (is_int($key))
		{
			unset($userIds[$key]);
		}

		// If there are no receivers, stop here
		if (empty($userIds))
		{
			return;
		}

		// Get the title of the stage
		$modelStage = $this->app->bootComponent('com_workflow')
			->getMVCFactory()->createModel('Stage', 'Administrator');

		$toStage = $modelStage->getItem($transition->to_stage_id)->title;

		// Get the name of the transition
		$modelTransition = $this->app->bootComponent('com_workflow')
			->getMVCFactory()->createModel('Transition', 'Administrator');

		$transitionName = $modelTransition->getItem($transition->id)->title;

		$hasGetItem = method_exists($model, 'getItem');
		$container  = Factory::getContainer();

		// Load language for messaging
		$lang = $container->get(LanguageFactoryInterface::class)
			->createLanguage($user->getParam('admin_language', $defaultLanguage), $debug);
		$lang->load('plg_workflow_notification');

		foreach ($pks as $pk)
		{
			// Get the title of the item which has changed, unknown as fallback
			$title = Text::_('PLG_WORKFLOW_NOTIFICATION_NO_TITLE');

			if ($hasGetItem)
			{
				$item  = $model->getItem($pk);
				$title = !empty($item->title) ? $item->title : $title;
			}

			$notificationTypes = $transition->options['notification_type'] ?? [];

			if (!is_array($notificationTypes))
			{
				$notificationTypes = (array) $notificationTypes;
			}

			// Send Email to receivers
			foreach ($userIds as $userId)
			{
				$recipient = $container->get(UserFactoryInterface::class)->loadUserById($userId);
				$extraText = '';

				if (!empty($transition->options['notification_text']))
				{
					$extraText = htmlspecialchars($lang->_($transition->options['notification_text']));
				}

				foreach ($notificationTypes as $type)
				{
					try
					{
						$functionName = 'send' . ucfirst($type);
						$this->$functionName(
							$recipient,
							$user->name,
							$title,
							$lang->_($transitionName),
							$lang->_($toStage),
							$lang,
							$extraText
						);
					}
					catch (Exception $exception)
					{
						$this->app->enqueueMessage($exception->getMessage(), 'error');
					}
				}
			}
		}

		$this->app->enqueueMessage(Text::_('PLG_WORKFLOW_NOTIFICATION_SENT'), 'message');
	}

	/**
	 * Send a message to com_messages when a stage changes transition.
	 *
	 * @param   \Joomla\CMS\User\User  $recipient       The user receiving the message
	 * @param   string                 $user            The user making the transition
	 * @param   string                 $title           The title of the item transitioned
	 * @param   string                 $transitionName  The name of the transition executed
	 * @param   string                 $toStage         The stage moving to
	 * @param   Language               $language        The language to use for translating the message
	 * @param   string                 $extraText       The additional text to add to the end of the message
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function sendMessages(
		\Joomla\CMS\User\User $recipient,
		string $user,
		string $title,
		string $transitionName,
		string $toStage,
		Language $language,
		string $extraText
	): void {
		if ($recipient->authorise('core.manage', 'com_message'))
		{
			// Get the model for private messages
			$modelMessage = $this->app->bootComponent('com_messages')
				->getMVCFactory()->createModel('Message', 'Administrator');

			// Remove users with locked input box from the list of receivers
			if ($this->isMessageBoxLocked($recipient->id))
			{
				return;
			}

			$subject     = sprintf($language->_('PLG_WORKFLOW_NOTIFICATION_ON_TRANSITION_SUBJECT'), $title);
			$messageText = sprintf($language->_('PLG_WORKFLOW_NOTIFICATION_ON_TRANSITION_MSG'),
				$title,
				$transitionName,
				$user,
				$toStage
			);
			$messageText .= '<br>' . $extraText;

			$message = [
				'id'         => 0,
				'user_id_to' => $recipient->id,
				'subject'    => $subject,
				'message'    => $messageText,
			];

			$modelMessage->save($message);
		}
	}

	/**
	 * Send an email when a stage changes transition.
	 *
	 * @param   \Joomla\CMS\User\User  $recipient       The user receiving the message
	 * @param   string                 $user            The user making the transition
	 * @param   string                 $title           The title of the item transitioned
	 * @param   string                 $transitionName  The name of the transition executed
	 * @param   string                 $toStage         The stage moving to
	 * @param   Language               $language        The language to use for translating the message
	 * @param   string                 $extraText       The additional text to add to the end of the message
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \PHPMailer\PHPMailer\Exception
	 */
	private function sendEmail(
		\Joomla\CMS\User\User $recipient,
		string $user,
		string $title,
		string $transitionName,
		string $toStage,
		Language $language,
		string $extraText
	): void {
		$data                   = [];
		$data['siteurl']        = Uri::base();
		$data['title']          = $title;
		$data['user']           = $user;
		$data['transitionName'] = $transitionName;
		$data['toStage']        = $toStage;
		$data['extraText']      = $extraText;

		$mailer = new MailTemplate('plg_workflow_notification.mail', $this->app->getLanguage()->getTag());
		$mailer->addTemplateData($data);
		$mailer->addRecipient($recipient->email);
		$mailer->send();
	}

	/**
	 * Get user_ids of receivers
	 *
	 * @param   stdClass  $data  Object containing data about the transition
	 *
	 * @return   array  $userIds  The receivers
	 *
	 * @since   4.0.0
	 * @throws  Exception
	 */
	private function getUsersFromGroup(stdClass $data): array
	{
		$users = [];

		// Single userIds
		if (!empty($data->options['notification_receivers']))
		{
			$users = ArrayHelper::toInteger($data->options['notification_receivers']);
		}

		// Usergroups
		$groups = [];

		if (!empty($data->options['notification_groups']))
		{
			$groups = ArrayHelper::toInteger($data->options['notification_groups']);
		}

		$users2 = [];

		if (!empty($groups))
		{
			// UserIds from usergroups
			$model = Factory::getApplication()->bootComponent('com_users')
				->getMVCFactory()->createModel('Users', 'Administrator', ['ignore_request' => true]);

			$model->setState('list.select', 'id');
			$model->setState('filter.groups', $groups);
			$model->setState('filter.state', 0);

			// Ids from usergroups
			$groupUsers = $model->getItems();

			$users2 = ArrayHelper::getColumn($groupUsers, 'id');
		}

		// Merge userIds from individual entries and userIDs from groups
		return array_unique(array_merge($users, $users2));
	}

	/**
	 * Check if the current plugin should execute workflow related activities
	 *
	 * @param   string  $context  The context to validate
	 *
	 * @return   boolean
	 *
	 * @since   4.0.0
	 */
	protected function isSupported(string $context): bool
	{
		if (!$this->checkAllowedAndForbiddenlist($context))
		{
			return false;
		}

		$parts = explode('.', $context);

		// We need at least the extension + view for loading the table fields
		if (count($parts) < 2)
		{
			return false;
		}

		$component = $this->app->bootComponent($parts[0]);

		if (!$component instanceof WorkflowServiceInterface
			|| !$component->isWorkflowActive($context))
		{
			return false;
		}

		return true;
	}

	/**
	 * Check if the message box is locked
	 *
	 * @param   int  $userId  The user ID which must be checked
	 *
	 * @return   boolean  Return status of message box is locked
	 *
	 * @since   4.0.0
	 */
	private function isMessageBoxLocked(int $userId): bool
	{
		if (empty($userId))
		{
			return false;
		}

		// Check for locked inboxes would be better to have _cdf settings in the user_object or a filter in users model
		$query = $this->db->getQuery(true);

		$query->select($this->db->quoteName('user_id'))
			->from($this->db->quoteName('#__messages_cfg'))
			->where($this->db->quoteName('user_id') . ' = ' . $userId)
			->where($this->db->quoteName('cfg_name') . ' = ' . $this->db->quote('locked'))
			->where($this->db->quoteName('cfg_value') . ' = 1');

		return (int) $this->db->setQuery($query)->loadResult() === $userId;
	}
}
