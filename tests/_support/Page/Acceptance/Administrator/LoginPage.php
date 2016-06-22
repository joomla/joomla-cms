<?php
namespace Page\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;

class LoginPage extends AdminPage
{
    public static $usernameField = ['css' => 'input[data-tests="username"]'];
    
    public static $passwordField = ['css' => 'input[data-tests="password"]'];
    
    public static $pageTitle = ['class' => 'page-title'];
    
    public static $loginButton = ['css' => 'button[data-tests="log in"]'];
    
    public static $pageURL = "/administrator/index.php";
}