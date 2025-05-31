<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\User\Profile\Extension;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Event\Model\PrepareDataEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Event\User\AfterDeleteEvent;
use Joomla\CMS\Event\User\AfterSaveEvent;
use Joomla\CMS\Event\User\BeforeSaveEvent;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * An example custom profile plugin.
 *
 * @since  1.6
 */
final class Profile extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Date of birth.
     *
     * @var    string
     *
     * @since  3.1
     */
    private $date = '';

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
            'onContentPrepareData' => 'onContentPrepareData',
            'onContentPrepareForm' => 'onContentPrepareForm',
            'onUserBeforeSave'     => 'onUserBeforeSave',
            'onUserAfterSave'      => 'onUserAfterSave',
            'onUserAfterDelete'    => 'onUserAfterDelete',
        ];
    }

    /**
     * Runs on content preparation
     *
     * @param   PrepareDataEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentPrepareData(PrepareDataEvent $event)
    {
        $context = $event->getContext();
        $data    = $event->getData();

        // Check we are manipulating a valid form.
        if (!\in_array($context, ['com_users.profile', 'com_users.user', 'com_users.registration'])) {
            return;
        }

        // Load plugin language files
        $this->loadLanguage();

        if (\is_object($data)) {
            $userId = $data->id ?? 0;

            if (!isset($data->profile) && $userId > 0) {
                // Load the profile data from the database.
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select(
                        [
                            $db->quoteName('profile_key'),
                            $db->quoteName('profile_value'),
                        ]
                    )
                    ->from($db->quoteName('#__user_profiles'))
                    ->where($db->quoteName('user_id') . ' = :userid')
                    ->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profile.%'))
                    ->order($db->quoteName('ordering'))
                    ->bind(':userid', $userId, ParameterType::INTEGER);

                $db->setQuery($query);
                $results = $db->loadRowList();

                // Merge the profile data.
                $data->profile = [];

                foreach ($results as $v) {
                    $k                 = str_replace('profile.', '', $v[0]);
                    $data->profile[$k] = json_decode($v[1], true);

                    if ($data->profile[$k] === null) {
                        $data->profile[$k] = $v[1];
                    }
                }
            }

            if (!HTMLHelper::isRegistered('users.url')) {
                HTMLHelper::register('users.url', [__CLASS__, 'url']);
            }

            if (!HTMLHelper::isRegistered('users.calendar')) {
                HTMLHelper::register('users.calendar', [__CLASS__, 'calendar']);
            }

            if (!HTMLHelper::isRegistered('users.tos')) {
                HTMLHelper::register('users.tos', [__CLASS__, 'tos']);
            }

            if (!HTMLHelper::isRegistered('users.dob')) {
                HTMLHelper::register('users.dob', [__CLASS__, 'dob']);
            }
        }
    }

    /**
     * Returns an anchor tag generated from a given value
     *
     * @param   string  $value  URL to use
     *
     * @return  mixed|string
     */
    public static function url($value)
    {
        if (empty($value)) {
            return HTMLHelper::_('users.value', $value);
        }

        // Convert website URL to utf8 for display
        $value = htmlspecialchars(PunycodeHelper::urlToUTF8($value), ENT_QUOTES, 'UTF-8');

        if (str_starts_with($value, 'http')) {
            return '<a href="' . $value . '">' . $value . '</a>';
        }

        return '<a href="http://' . $value . '">' . $value . '</a>';
    }

    /**
     * Returns html markup showing a date picker
     *
     * @param   string  $value  valid date string
     *
     * @return  mixed
     */
    public static function calendar($value)
    {
        if (empty($value)) {
            return HTMLHelper::_('users.value', $value);
        }

        return HTMLHelper::_('date', $value, null, null);
    }

    /**
     * Returns the date of birth formatted and calculated using server timezone.
     *
     * @param   string  $value  valid date string
     *
     * @return  mixed
     */
    public static function dob($value)
    {
        if (!$value) {
            return '';
        }

        return HTMLHelper::_('date', $value, Text::_('DATE_FORMAT_LC1'), false);
    }

    /**
     * Return the translated strings yes or no depending on the value
     *
     * @param   boolean  $value  input value
     *
     * @return  string
     */
    public static function tos($value)
    {
        if ($value) {
            return Text::_('JYES');
        }

        return Text::_('JNO');
    }

    /**
     * Adds additional fields to the user editing form
     *
     * @param   PrepareFormEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentPrepareForm(PrepareFormEvent $event)
    {
        $form = $event->getForm();
        // Check we are manipulating a valid form.
        $name = $form->getName();

        if (!\in_array($name, ['com_users.user', 'com_users.profile', 'com_users.registration'])) {
            return;
        }

        // Load plugin language files
        $this->loadLanguage();

        // Add the registration fields to the form.
        FormHelper::addFieldPrefix('Joomla\\Plugin\\User\\Profile\\Field');
        FormHelper::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');
        $form->loadFile('profile');

        $fields = [
            'address1',
            'address2',
            'city',
            'region',
            'country',
            'postal_code',
            'phone',
            'website',
            'favoritebook',
            'aboutme',
            'dob',
            'tos',
        ];

        $tosArticle = $this->params->get('register_tos_article');
        $tosEnabled = $this->params->get('register-require_tos', 0);

        // We need to be in the registration form and field needs to be enabled
        if ($name !== 'com_users.registration' || !$tosEnabled) {
            // We only want the TOS in the registration form
            $form->removeField('tos', 'profile');
        } else {
            // Push the TOS article ID into the TOS field.
            $form->setFieldAttribute('tos', 'article', $tosArticle, 'profile');
        }

        foreach ($fields as $field) {
            // Case using the users manager in admin
            if ($name === 'com_users.user') {
                // Toggle whether the field is required.
                if ($this->params->get('profile-require_' . $field, 1) > 0) {
                    $form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profile');
                } elseif (
                    // Remove the field if it is disabled in registration and profile
                    $this->params->get('register-require_' . $field, 1) == 0
                    && $this->params->get('profile-require_' . $field, 1) == 0
                ) {
                    $form->removeField($field, 'profile');
                }
            } elseif ($name === 'com_users.registration') {
                // Case registration
                // Toggle whether the field is required.
                if ($this->params->get('register-require_' . $field, 1) > 0) {
                    $form->setFieldAttribute($field, 'required', ($this->params->get('register-require_' . $field) == 2) ? 'required' : '', 'profile');
                } else {
                    $form->removeField($field, 'profile');
                }
            } elseif ($name === 'com_users.profile') {
                // Case profile in site or admin
                // Toggle whether the field is required.
                if ($this->params->get('profile-require_' . $field, 1) > 0) {
                    $form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profile');
                } else {
                    $form->removeField($field, 'profile');
                }
            }
        }

        // Drop the profile form entirely if there aren't any fields to display.
        $remainingfields = $form->getGroup('profile');

        if (!\count($remainingfields)) {
            $form->removeGroup('profile');
        }
    }

    /**
     * Method is called before user data is stored in the database
     *
     * @param   BeforeSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.1
     * @throws  \InvalidArgumentException on invalid date.
     */
    public function onUserBeforeSave(BeforeSaveEvent $event)
    {
        $data = $event->getData();

        // Load plugin language files
        $this->loadLanguage();

        // Check that the date is valid.
        if (!empty($data['profile']['dob'])) {
            try {
                $date       = new Date($data['profile']['dob']);
                $this->date = $date->format('Y-m-d H:i:s');
            } catch (\Exception) {
                // Throw an exception if date is not valid.
                throw new \InvalidArgumentException($this->getApplication()->getLanguage()->_('PLG_USER_PROFILE_ERROR_INVALID_DOB'));
            }

            if (Date::getInstance('now') < $date) {
                // Throw an exception if dob is greater than now.
                throw new \InvalidArgumentException($this->getApplication()->getLanguage()->_('PLG_USER_PROFILE_ERROR_INVALID_DOB_FUTURE_DATE'));
            }
        }

        // Check that the tos is checked if required ie only in registration from frontend.
        $task       = $this->getApplication()->getInput()->getCmd('task');
        $option     = $this->getApplication()->getInput()->getCmd('option');
        $tosEnabled = ($this->params->get('register-require_tos', 0) == 2);

        // Check that the tos is checked.
        if ($task === 'register' && $tosEnabled && $option === 'com_users' && !$data['profile']['tos']) {
            throw new \InvalidArgumentException($this->getApplication()->getLanguage()->_('PLG_USER_PROFILE_FIELD_TOS_DESC_SITE'));
        }
    }

    /**
     * Saves user profile data
     *
     * @param   AfterSaveEvent $event  The event instance.
     *
     * @return  void
     */
    public function onUserAfterSave(AfterSaveEvent $event): void
    {
        $data   = $event->getUser();
        $result = $event->getSavingResult();
        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');

        if ($userId && $result && isset($data['profile']) && \count($data['profile'])) {
            $db = $this->getDatabase();

            // Sanitize the date
            if (!empty($data['profile']['dob'])) {
                $data['profile']['dob'] = $this->date;
            }

            $keys = array_keys($data['profile']);

            foreach ($keys as &$key) {
                $key = 'profile.' . $key;
            }

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('user_id') . ' = :userid')
                ->whereIn($db->quoteName('profile_key'), $keys, ParameterType::STRING)
                ->bind(':userid', $userId, ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->select($db->quoteName('ordering'))
                ->from($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('user_id') . ' = :userid')
                ->bind(':userid', $userId, ParameterType::INTEGER);
            $db->setQuery($query);
            $usedOrdering = $db->loadColumn();

            $order = 1;
            $query->clear()
                ->insert($db->quoteName('#__user_profiles'));

            foreach ($data['profile'] as $k => $v) {
                while (\in_array($order, $usedOrdering)) {
                    $order++;
                }

                $query->values(
                    implode(
                        ',',
                        $query->bindArray(
                            [
                                $userId,
                                'profile.' . $k,
                                json_encode($v),
                                $order++,
                            ],
                            [
                                ParameterType::INTEGER,
                                ParameterType::STRING,
                                ParameterType::STRING,
                                ParameterType::INTEGER,
                            ]
                        )
                    )
                );
            }

            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Remove all user profile information for the given user ID
     *
     * Method is called after user data is deleted from the database
     *
     * @param   AfterDeleteEvent $event  The event instance.
     *
     * @return  void
     */
    public function onUserAfterDelete(AfterDeleteEvent $event): void
    {
        $user    = $event->getUser();
        $success = $event->getDeletingResult();

        if (!$success) {
            return;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        if ($userId) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('user_id') . ' = :userid')
                ->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profile.%'))
                ->bind(':userid', $userId, ParameterType::INTEGER);

            $db->setQuery($query);
            $db->execute();
        }
    }
}
