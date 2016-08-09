<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once JPATH_COMPONENT . '/controller.php';

/**
 * Profile controller class for Users.
 *
 * @since  1.6
 */
class UsersControllerRegistration extends UsersController
{
    /**
     *
     * Method to validate a username.
     * @return  void
     * @since 3.7
     *
     */

    public function validate()
    {
        //read username from ajax
        $username = $this->input->get('username', '', 'username');
        $email = $this->input->get('email', '', 'email');

        //check
        if(!empty($username))
        {
            $fieldname = 'username';
            $field = $username;
        }
        else if(!empty($email))
        {
            $fieldname = 'email';
            $field = $email;
        }

        if(isset($fieldname) && isset($field))
        {
            $db = JFactory::getDbo();

            $valid = $db->setQuery(
                $db->getQuery(true)
                    ->select($fieldname)
                    ->from('#__users')
                    ->where($fieldname . ' = ' . $db->quote($field))
            )->loadResult();

            //return jsonarray with results
            $msg = null;
            if(!is_null($valid))
            {
                if($fieldname == 'username')
                {
                    $msg = JText::_('COM_USERS_REGISTER_USERNAME_MESSAGE');
                }else if($fieldname == 'email')
                {
                    $msg = JText::_('COM_USERS_PROFILE_EMAIL1_MESSAGE');
                }
            }
            echo new JResponseJson($valid, $msg, is_null($valid));
        }


    }
}