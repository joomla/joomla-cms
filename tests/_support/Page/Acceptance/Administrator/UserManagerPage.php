<?php
namespace Page\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;

class UserManagerPage extends AdminPage
{
    public static $url = "administrator/index.php?option=com_users&view=users";

    public static $pageTitleText = "Users";

    public static $nameField = ['id' => 'jform_name'];

    public static $usernameField = ['id' => 'jform_username'];

    public static $passwordField = ['id' => 'jform_password'];

    public static $password1Field = ['id' => 'jform_password1'];

    public static $password2Field = ['id' => 'jform_password2'];

    public static $emailField = ['id' => 'jform_email'];

    public static $email1Field = ['id' => 'jform_email1'];

    public static $email2Field = ['id' => 'jform_email2'];

    public static $filterSearch = ['id' => 'filter_search'];

    public static $iconSearch = ['class' => 'icon-search'];

    public static $title = ['id' => 'jform_title'];

    public static $seeUserName = ['xpath' => "//table[@id='userList']//tr[1]/td[3]"];

    public static $seeName = ['xpath' => "//table[@id='userList']//tr[1]/td[2]"];

    public static $lastLoginDate = ['xpath' => "//table[@id='userList']//tr[1]/td[8]"];

}
