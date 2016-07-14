<?php
namespace Page\Acceptance\Site;

class Frontpage extends \AcceptanceTester
{
    public static $url = '/';

    public static $alertMessage = ['class' => 'alert-message'];

    public static $loginGreeting = ['class' => 'login-greeting'];
}
