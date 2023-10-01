<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.notification
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Workflow\Notification\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Workflow\WorkflowTransitionEvent;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\CMS\Workflow\WorkflowPluginTrait;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Workflow Notification Plugin
 *
 * @since  4.0.0
 */
final class Notification extends CMSPlugin implements SubscriberInterface
{
    use WorkflowPluginTrait;
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * The language factory.
     *
     * @var    LanguageFactoryInterface
     * @since  4.4.0
     */
    private $languageFactory;

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
        ];
    }


    /**
     * Constructor.
     *
     * @param   DispatcherInterface       $dispatcher       The dispatcher
     * @param   array                     $config           An optional associative array of configuration settings
     * @param   LanguageFactoryInterface  $languageFactory  The language factory
     *
     * @since   4.2.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, LanguageFactoryInterface $languageFactory)
    {
        parent::__construct($dispatcher, $config);

        $this->languageFactory = $languageFactory;
    }

    /**
     * The form event.
     *
     * @param   Form      $form  The form
     * @param   stdClass  $data  The data
     *
     * @return   boolean
     *
     * @since   4.0.0
     */
    public function onContentPrepareForm(EventInterface $event)
    {
        [$form, $data] = array_values($event->getArguments());

        $context = $form->getName();

        // Extend the transition form
        if ($context === 'com_workflow.transition') {
            $this->enhanceWorkflowTransitionForm($form, $data);
        }

        return true;
    }

    /**
     * Send a Notification to defined users a transition is performed
     *
     * @param   WorkflowTransitionEvent  $event  The workflow event being processed.
     *
     * @return   void
     *
     * @since   4.0.0
     */
    public function onWorkflowAfterTransition(WorkflowTransitionEvent $event)
    {
        $context       = $event->getArgument('extension');
        $extensionName = $event->getArgument('extensionName');
        $transition    = $event->getArgument('transition');
        $pks           = $event->getArgument('pks');

        if (!$this->isSupported($context)) {
            return;
        }

        $component = $this->getApplication()->bootComponent($extensionName);

        // Check if send-mail is active
        if (empty($transition->options['notification_send_mail'])) {
            return;
        }

        // ID of the items whose state has changed.
        $pks = ArrayHelper::toInteger($pks);

        if (empty($pks)) {
            return;
        }

        // Get UserIds of Receivers
        $userIds = $this->getUsersFromGroup($transition);

        // The active user
        $user = $this->getApplication()->getIdentity();

        // Prepare Language for messages
        $defaultLanguage = ComponentHelper::getParams('com_languages')->get('administrator');
        $debug           = $this->getApplication()->get('debug_lang');

        $modelName = $component->getModelName($context);
        $model     = $component->getMVCFactory()->createModel($modelName, $this->getApplication()->getName(), ['ignore_request' => true]);

        // Don't send the notification to the active user
        $key = array_search($user->id, $userIds);

        if (is_int($key)) {
            unset($userIds[$key]);
        }

        // Remove users with locked input box from the list of receivers
        if (!empty($userIds)) {
            $userIds = $this->removeLocked($userIds);
        }

        // If there are no receivers, stop here
        if (empty($userIds)) {
            $this->getApplication()->enqueueMessage($this->getApplication()->getLanguage()->_('PLG_WORKFLOW_NOTIFICATION_NO_RECEIVER'), 'error');

            return;
        }

        // Get the model for private messages
        $model_message = $this->getApplication()->bootComponent('com_messages')
            ->getMVCFactory()->createModel('Message', 'Administrator');

        // Get the title of the stage
        $model_stage = $this->getApplication()->bootComponent('com_workflow')
            ->getMVCFactory()->createModel('Stage', 'Administrator');

        $toStage = $model_stage->getItem($transition->to_stage_id)->title;

        // Get the name of the transition
        $model_transition = $this->getApplication()->bootComponent('com_workflow')
            ->getMVCFactory()->createModel('Transition', 'Administrator');

        $transitionName = $model_transition->getItem($transition->id)->title;

        $hasGetItem = method_exists($model, 'getItem');

        foreach ($pks as $pk) {
            // Get the title of the item which has changed, unknown as fallback
            $title = $this->getApplication()->getLanguage()->_('PLG_WORKFLOW_NOTIFICATION_NO_TITLE');

            if ($hasGetItem) {
                $item  = $model->getItem($pk);
                $title = !empty($item->title) ? $item->title : $title;
            }

            // Send Email to receivers
            foreach ($userIds as $user_id) {
                $receiver = $this->getUserFactory()->loadUserById($user_id);

                if ($receiver->authorise('core.manage', 'com_messages')) {
                    // Load language for messaging
                    $lang = $this->languageFactory->createLanguage($user->getParam('admin_language', $defaultLanguage), $debug);
                    $lang->load('plg_workflow_notification');
                    $messageText = sprintf(
                        $lang->_('PLG_WORKFLOW_NOTIFICATION_ON_TRANSITION_MSG'),
                        $title,
                        $lang->_($transitionName),
                        $user->name,
                        $lang->_($toStage)
                    );

                    if (!empty($transition->options['notification_text'])) {
                        $messageText .= '<br>' . htmlspecialchars($lang->_($transition->options['notification_text']));
                    }

                    $message = [
                        'id'         => 0,
                        'user_id_to' => $receiver->id,
                        'subject'    => sprintf($lang->_('PLG_WORKFLOW_NOTIFICATION_ON_TRANSITION_SUBJECT'), $title),
                        'message'    => $messageText,
                    ];

                    $model_message->save($message);
                }
            }
        }

        $this->getApplication()->enqueueMessage($this->getApplication()->getLanguage()->_('PLG_WORKFLOW_NOTIFICATION_SENT'), 'message');
    }

    /**
     * Get user_ids of receivers
     *
     * @param   object  $data  Object containing data about the transition
     *
     * @return   array  $userIds  The receivers
     *
     * @since   4.0.0
     */
    private function getUsersFromGroup($data): array
    {
        $users = [];

        // Single userIds
        if (!empty($data->options['notification_receivers'])) {
            $users = ArrayHelper::toInteger($data->options['notification_receivers']);
        }

        // Usergroups
        $groups = [];

        if (!empty($data->options['notification_groups'])) {
            $groups = ArrayHelper::toInteger($data->options['notification_groups']);
        }

        $users2 = [];

        if (!empty($groups)) {
            // UserIds from usergroups
            $model = $this->getApplication()->bootComponent('com_users')
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
     * @param   string  $context
     *
     * @return   boolean
     *
     * @since   4.0.0
     */
    protected function isSupported($context)
    {
        if (!$this->checkAllowedAndForbiddenlist($context)) {
            return false;
        }

        $parts = explode('.', $context);

        // We need at least the extension + view for loading the table fields
        if (count($parts) < 2) {
            return false;
        }

        $component = $this->getApplication()->bootComponent($parts[0]);

        if (!$component instanceof WorkflowServiceInterface || !$component->isWorkflowActive($context)) {
            return false;
        }

        return true;
    }

    /**
     * Remove receivers who have locked their message inputbox
     *
     * @param   array  $userIds  The userIds which must be checked
     *
     * @return   array  users with active message input box
     *
     * @since   4.0.0
     */
    private function removeLocked(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $db = $this->getDatabase();

        // Check for locked inboxes would be better to have _cdf settings in the user_object or a filter in users model
        $query = $db->getQuery(true);

        $query->select($db->quoteName('user_id'))
            ->from($db->quoteName('#__messages_cfg'))
            ->whereIn($db->quoteName('user_id'), $userIds)
            ->where($db->quoteName('cfg_name') . ' = ' . $db->quote('locked'))
            ->where($db->quoteName('cfg_value') . ' = 1');

        $locked = $db->setQuery($query)->loadColumn();

        return array_diff($userIds, $locked);
    }
}
