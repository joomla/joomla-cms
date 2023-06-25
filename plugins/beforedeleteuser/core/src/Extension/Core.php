<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Jtchuserbeforedel
 *
 * @author      Guido De Gobbis <support@joomtools.de>
 * @copyright   Copyright JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

namespace Joomla\Plugin\BeforeDeleteUser\Core\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\BeforeDeleteUserTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\User\BeforeDeleteUserInterface;

/**
 * Class to support the core extension 'com_content'.
 *
 * @since  __DEPLOY_VERSION__
 */
final class Core extends CMSPlugin implements SubscriberInterface, BeforeDeleteUserInterface
{
    use BeforeDeleteUserTrait;
    use DatabaseAwareTrait;

    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents()
    : array
    {
        return [
            'onUserBeforeDelete' => 'onUserBeforeDelete',
        ];
    }

    /**
     * Event triggered before the user is deleted.
     *
     * @param   Event  $event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onUserBeforeDelete(Event $event)
    {
        /** @var   array $user The user to be altered. */
        [$user, $params] = $event->getArguments();

        $this->changeUser($params);
    }

    /**
     * The list of database table and columns, where the user information to change.
     *
     * @return  array[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTablesListToChangeUser()
    {
        return array(
            array(
                'baseContext' => 'com_banners',
                'realName'    => 'com_banners',
                'tableName'   => '#__banners',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_by',
                    'modified_by',
                ),
                'userName'    => array(
                    'created_by_alias',
                ),
            ),

            array(
                'baseContext' => 'com_categories',
                'realName'    => 'com_categories',
                'tableName'   => '#__categories',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_user_id',
                    'modified_user_id',
                ),
            ),

            array(
                'baseContext' => 'com_contact',
                'realName'    => 'com_contact',
                'tableName'   => '#__contact_details',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'user_id',
                    'created_by',
                    'modified_by',
                ),
                'userName'    => array(
                    'created_by_alias',
                ),
            ),

            array(
                'baseContext' => 'com_contenthistory',
                'realName'    => 'com_contenthistory',
                'tableName'   => '#__history',
                'primaryKey'  => 'version_id',
                'userId'      => array(
                    'editor_user_id',
                ),
            ),

            array(
                'baseContext' => 'com_content',
                'realName'    => 'com_content',
                'tableName'   => '#__content',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_by',
                    'modified_by',
                ),
                'userName'    => array(
                    'created_by_alias',
                ),
            ),

            array(
                'baseContext' => 'com_fields',
                'realName'    => 'com_fields',
                'tableName'   => '#__fields',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_user_id',
                    'modified_by',
                ),
            ),
            array(
                'baseContext' => 'com_fields',
                'realName'    => 'com_fields',
                'tableName'   => '#__fields_groups',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_by',
                    'modified_by',
                ),
            ),

            array(
                'baseContext' => 'com_finder',
                'realName'    => 'com_finder',
                'tableName'   => '#__finder_filters',
                'primaryKey'  => 'filter_id',
                'userId'      => array(
                    'created_by',
                    'modified_by',
                ),
            ),

            array(
                'baseContext' => 'com_guidedtours',
                'realName'    => 'com_guidedtours',
                'tableName'   => '#__guidedtours',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_by',
                    'modified_by',
                ),
            ),
            array(
                'baseContext' => 'com_guidedtours',
                'realName'    => 'com_guidedtours',
                'tableName'   => '#__guidedtour_steps',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_by',
                    'modified_by',
                ),
            ),

            array(
                'baseContext' => 'com_messages',
                'realName'    => 'com_messages',
                'tableName'   => '#__messages',
                'primaryKey'  => 'message_id',
                'userId'      => array(
                    'user_id_from',
                    'user_id_to',
                ),
            ),
            array(
                'baseContext' => 'com_messages',
                'realName'    => 'com_messages',
                'tableName'   => '#__messages_cfg',
                'primaryKey'  => 'user_id',
                'userId'      => array(
                    'user_id',
                ),
            ),

            array(
                'baseContext' => 'com_newsfeeds',
                'realName'    => 'com_newsfeeds',
                'tableName'   => '#__newsfeeds',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_by',
                    'modified_by',
                ),
                'userName'    => array(
                    'created_by_alias',
                ),
            ),

            array(
                'baseContext' => 'com_privacy',
                'realName'    => 'com_privacy',
                'tableName'   => '#__privacy_consents',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'user_id',
                ),
            ),

            array(
                'baseContext' => 'com_scheduler',
                'realName'    => 'com_scheduler',
                'tableName'   => '#__scheduler_tasks',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_by',
                ),
            ),

            array(
                'baseContext' => 'com_tags',
                'realName'    => 'com_tags',
                'tableName'   => '#__tags',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_user_id',
                    'modified_user_id',
                ),
                'userName'    => array(
                    'created_by_alias',
                ),
            ),

            array(
                'baseContext' => 'com_workflow',
                'realName'    => 'com_workflow',
                'tableName'   => '#__workflows',
                'primaryKey'  => 'id',
                'userId'      => array(
                    'created_by',
                    'modified_by',
                ),
            ),
        );
    }
}
