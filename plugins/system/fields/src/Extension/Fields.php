<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Fields\Extension;

use Joomla\CMS\Event\Content;
use Joomla\CMS\Event\Model;
use Joomla\CMS\Event\User;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Plugin
 *
 * @since  3.7
 */
final class Fields extends CMSPlugin implements SubscriberInterface
{
    use UserFactoryAwareTrait;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentNormaliseRequestData' => 'onContentNormaliseRequestData',
            'onContentPrepare'              => 'onContentPrepare',
            'onContentPrepareForm'          => 'onContentPrepareForm',
            'onContentAfterSave'            => 'onContentAfterSave',
            'onContentAfterDelete'          => 'onContentAfterDelete',
            'onUserAfterSave'               => 'onUserAfterSave',
            'onUserAfterDelete'             => 'onUserAfterDelete',
            'onContentAfterTitle'           => 'onContentAfterTitle',
            'onContentBeforeDisplay'        => 'onContentBeforeDisplay',
            'onContentAfterDisplay'         => 'onContentAfterDisplay',
        ];
    }

    /**
     * Normalizes the request data.
     *
     * @param   Model\NormaliseRequestDataEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.8.7
     */
    public function onContentNormaliseRequestData(Model\NormaliseRequestDataEvent $event)
    {
        $context = $event->getContext();
        $data    = $event->getData();
        $form    = $event->getForm();

        if (!FieldsHelper::extract($context, $data)) {
            return;
        }

        // Loop over all fields
        foreach ($form->getGroup('com_fields') as $field) {
            if ($field->disabled === true) {
                /**
                 * Disabled fields should NEVER be added to the request as
                 * they should NEVER be added by the browser anyway so nothing to check against
                 * as "disabled" means no interaction at all.
                 */

                // Make sure the data object has an entry before delete it
                if (isset($data->com_fields[$field->fieldname])) {
                    unset($data->com_fields[$field->fieldname]);
                }

                continue;
            }

            // Make sure the data object has an entry
            if (isset($data->com_fields[$field->fieldname])) {
                continue;
            }

            // Set a default value for the field
            $data->com_fields[$field->fieldname] = false;
        }
    }

    /**
     * The save event.
     *
     * @param   Model\AfterSaveEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentAfterSave(Model\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();
        $data    = $event->getData();

        // Check if data is an array and the item has an id
        if (!\is_array($data) || empty($item->id) || empty($data['com_fields'])) {
            return;
        }

        // Create correct context for category
        if ($context === 'com_categories.category') {
            $context = $item->extension . '.categories';

            // Set the catid on the category to get only the fields which belong to this category
            $item->catid = $item->id;
        }

        // Check the context
        $parts = FieldsHelper::extract($context, $item);

        if (!$parts) {
            return;
        }

        // Compile the right context for the fields
        $context = $parts[0] . '.' . $parts[1];

        // Loading the fields
        $fields = FieldsHelper::getFields($context, $item);

        if (!$fields) {
            return;
        }

        // Loading the model

        /** @var \Joomla\Component\Fields\Administrator\Model\FieldModel $model */
        $model = $this->getApplication()->bootComponent('com_fields')->getMVCFactory()
            ->createModel('Field', 'Administrator', ['ignore_request' => true]);

        // Loop over the fields
        foreach ($fields as $field) {
            // Determine the value if it is (un)available from the data
            if (\array_key_exists($field->name, $data['com_fields'])) {
                $value = $data['com_fields'][$field->name] === false ? null : $data['com_fields'][$field->name];
            } else {
                // Field not available on form, use stored value
                $value = $field->rawvalue;
            }

            // If no value set (empty) remove value from database
            if (\is_array($value) ? !\count($value) : !\strlen($value)) {
                $value = null;
            }

            // JSON encode value for complex fields
            if (\is_array($value) && (\count($value, COUNT_NORMAL) !== \count($value, COUNT_RECURSIVE) || !\count(array_filter(array_keys($value), 'is_numeric')))) {
                $value = json_encode($value);
            }

            // Setting the value for the field and the item
            $model->setFieldValue($field->id, $item->id, $value);
        }
    }

    /**
     * The save event.
     *
     * @param   User\AfterSaveEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onUserAfterSave(User\AfterSaveEvent $event): void
    {
        $userData = $event->getUser();
        $success  = $event->getSavingResult();

        // It is not possible to manipulate the user during save events
        // Check if data is valid or we are in a recursion
        if (!$userData['id'] || !$success) {
            return;
        }

        $user = $this->getUserFactory()->loadUserById($userData['id']);

        $task = $this->getApplication()->getInput()->getCmd('task');

        // Skip fields save when we activate a user, because we will lose the saved data
        if (\in_array($task, ['activate', 'block', 'unblock'])) {
            return;
        }

        // Trigger the events with a real user
        $contentEvent = new Model\AfterSaveEvent('onContentAfterSave', [
            'context' => 'com_users.user',
            'subject' => $user,
            'isNew'   => false,
            'data'    => $userData,
        ]);
        $this->onContentAfterSave($contentEvent);
    }

    /**
     * The delete event.
     *
     * @param   Model\AfterDeleteEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentAfterDelete(Model\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();

        // Set correct context for category
        if ($context === 'com_categories.category') {
            $context = $item->extension . '.categories';
        }

        $parts = FieldsHelper::extract($context, $item);

        if (!$parts || empty($item->id)) {
            return;
        }

        $context = $parts[0] . '.' . $parts[1];

        /** @var \Joomla\Component\Fields\Administrator\Model\FieldModel $model */
        $model = $this->getApplication()->bootComponent('com_fields')->getMVCFactory()
            ->createModel('Field', 'Administrator', ['ignore_request' => true]);
        $model->cleanupValues($context, $item->id);
    }

    /**
     * The user delete event.
     *
     * @param   User\AfterDeleteEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onUserAfterDelete(User\AfterDeleteEvent $event): void
    {
        $user     = $event->getUser();
        $item     = new \stdClass();
        $item->id = $user['id'];

        $contentEvent = new Model\AfterDeleteEvent('onContentAfterDelete', [
            'context' => 'com_users.user',
            'subject' => $item,
        ]);
        $this->onContentAfterDelete($contentEvent);
    }

    /**
     * The form event.
     *
     * @param   Model\PrepareFormEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentPrepareForm(Model\PrepareFormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $event->getData();
        $context = $form->getName();

        // When a category is edited, the context is com_categories.categorycom_content
        if (str_starts_with($context, 'com_categories.category')) {
            $context = str_replace('com_categories.category', '', $context) . '.categories';
            $data    = $data ?: $this->getApplication()->getInput()->get('jform', [], 'array');

            // Set the catid on the category to get only the fields which belong to this category
            if (\is_array($data) && \array_key_exists('id', $data)) {
                $data['catid'] = $data['id'];
            }

            if (\is_object($data) && isset($data->id)) {
                $data->catid = $data->id;
            }
        }

        $parts = FieldsHelper::extract($context, $form);

        if (!$parts) {
            return;
        }

        $input = $this->getApplication()->getInput();

        // If we are on the save command we need the actual data
        $jformData = $input->get('jform', [], 'array');

        if ($jformData && !$data) {
            $data = $jformData;
        }

        if (\is_array($data)) {
            $data = (object) $data;
        }

        FieldsHelper::prepareForm($parts[0] . '.' . $parts[1], $form, $data);
    }

    /**
     * The display event.
     *
     * @param   Content\AfterTitleEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentAfterTitle(Content\AfterTitleEvent $event)
    {
        $event->addResult($this->display($event->getContext(), $event->getItem(), $event->getParams(), 1));
    }

    /**
     * The display event.
     *
     * @param   Content\BeforeDisplayEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentBeforeDisplay(Content\BeforeDisplayEvent $event)
    {
        $event->addResult($this->display($event->getContext(), $event->getItem(), $event->getParams(), 2));
    }

    /**
     * The display event.
     *
     * @param   Content\AfterDisplayEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentAfterDisplay(Content\AfterDisplayEvent $event)
    {
        $event->addResult($this->display($event->getContext(), $event->getItem(), $event->getParams(), 3));
    }

    /**
     * Performs the display event.
     *
     * @param   string    $context      The context
     * @param   \stdClass  $item         The item
     * @param   Registry  $params       The params
     * @param   integer   $displayType  The type
     *
     * @return  string
     *
     * @since   3.7.0
     */
    private function display($context, $item, $params, $displayType)
    {
        $parts = FieldsHelper::extract($context, $item);

        if (!$parts) {
            return '';
        }

        // If we have a category, set the catid field to fetch only the fields which belong to it
        if ($parts[1] === 'categories' && !isset($item->catid)) {
            $item->catid = $item->id;
        }

        $context = $parts[0] . '.' . $parts[1];

        // Convert tags
        if ($context == 'com_tags.tag' && !empty($item->type_alias)) {
            // Set the context
            $context = $item->type_alias;

            $item = $this->prepareTagItem($item);
        }

        if (\is_string($params) || !$params) {
            $params = new Registry($params);
        }

        $fields = FieldsHelper::getFields($context, $item, $displayType);

        if ($fields) {
            if ($this->getApplication()->isClient('site') && Multilanguage::isEnabled() && isset($item->language) && $item->language === '*') {
                $lang = $this->getApplication()->getLanguage()->getTag();

                foreach ($fields as $key => $field) {
                    if ($field->language === '*' || $field->language == $lang) {
                        continue;
                    }

                    unset($fields[$key]);
                }
            }
        }

        if ($fields) {
            foreach ($fields as $key => $field) {
                $fieldDisplayType = $field->params->get('display', '2');

                if ($fieldDisplayType == $displayType) {
                    continue;
                }

                unset($fields[$key]);
            }
        }

        if ($fields) {
            return FieldsHelper::render(
                $context,
                'fields.render',
                [
                    'item'    => $item,
                    'context' => $context,
                    'fields'  => $fields,
                ]
            );
        }

        return '';
    }

    /**
     * Performs the display event.
     *
     * @param   Content\ContentPrepareEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentPrepare(Content\ContentPrepareEvent $event)
    {
        $context = $event->getContext();
        $item    = $event->getItem();

        // Check property exists (avoid costly & useless recreation), if need to recreate them, just unset the property!
        if (isset($item->jcfields)) {
            return;
        }

        $parts = FieldsHelper::extract($context, $item);

        if (!$parts) {
            return;
        }

        $context = $parts[0] . '.' . $parts[1];

        // Convert tags
        if ($context == 'com_tags.tag' && !empty($item->type_alias)) {
            // Set the context
            $context = $item->type_alias;

            $item = $this->prepareTagItem($item);
        }

        // Get item's fields, also preparing their value property for manual display
        // (calling plugins events and loading layouts to get their HTML display)
        $fields = FieldsHelper::getFields($context, $item, true);

        // Adding the fields to the object
        $item->jcfields = [];

        foreach ($fields as $key => $field) {
            $item->jcfields[$field->id] = $field;
        }
    }

    /**
     * Prepares a tag item to be ready for com_fields.
     *
     * @param   \stdClass  $item  The item
     *
     * @return  object
     *
     * @since   3.8.4
     */
    private function prepareTagItem($item)
    {
        // Map core fields
        $item->id       = $item->content_item_id;
        $item->language = $item->core_language;

        // Also handle the catid
        if (!empty($item->core_catid)) {
            $item->catid = $item->core_catid;
        }

        return $item;
    }
}
