<?php
namespace Page\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;

class UserManagerPage extends AdminPage
{
    public static $nameField = ['id' => 'jform_name'];

    public static $usernameField = ['id' => 'jform_username'];

    public static $passwordField = ['id' => 'jform_password'];

    public static $password2Field = ['id' => 'jform_password2'];

    public static $emailField = ['id' => 'jform_email'];
    
    public static $filterSearch = ['id' => 'filter_search'];

    public static $iconSearch = ['class' => 'icon-search'];

    public static $title = ['id' => 'jform_title'];
    
    public static $pageURL = "administrator/index.php?option=com_users&view=users";
}
