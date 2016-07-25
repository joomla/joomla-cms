<?php
namespace Page\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;

class LoginPage extends AdminPage
{
    public static $url = "/administrator/index.php";

    /**
     * @var   array  Locator for username login form textfield
     */
    public static $usernameField = ['id' => 'mod-login-username'];

    /**
     * @var   array  Locator for password login form textfield
     */
    public static $passwordField = ['id' => 'mod-login-password'];

    /**
     * @var   array  Locator for Log in button
     */
    public static $loginButton = ['xpath' => "//button[contains(normalize-space(), 'Log in')]"];
}